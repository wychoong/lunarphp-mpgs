<?php

namespace WyChoong\Mpgs\Clients;

use Closure;
use Exception;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class Mpgs
{
    protected static string $gateway;

    protected static string $merchantId;

    protected static string $apiPassword;

    protected static string $version;

    protected static Closure|array $setupUsing = [];

    public function __construct()
    {
        $this->setupClient();
    }

    public static function setupClientUsing(Closure|array $setupUsing)
    {
        static::$setupUsing = $setupUsing;
    }

    public static function setupClient()
    {
        $params = static::$setupUsing;

        if ($params instanceof Closure) {
            $params = $params();
        }

        foreach ($params as $key => $value) {
            if (in_array($key, ['gateway', 'merchantId', 'apiPassword', 'version']) && filled($value)) {
                static::$$key = $value;
            }
        }

        static::$gateway ??= static::config('gateway');
        static::$merchantId ??= static::config('merchant_id');
        static::$apiPassword ??= static::config('api_password');
        static::$version ??= static::config('version');
    }

    public static function config($key, $default = null): ?string
    {
        $value = config("lunar-mpgs.{$key}", $default);

        if (!$value && str($key)->contains('action.')) {
            $action = str($key)->afterLast('action.')->toString();
            $value = match ($action) {
                'initiate_checkout' => '/session',
                'retrieve_session' => '/session/{sessionId}',
                'retrieve_order' => '/order/{orderId}',
                default => throw new Exception('Invalid action'),
            };
        }

        return $value;
    }

    protected function getUrl(string $action): string
    {
        return '{+gateway}/version/{version}/merchant/{merchantId}' . self::config("action.{$action}");
    }

    protected function execute(string $method, string $action, array $data = [], array $urlParams = []): Response
    {
        $client = Http::withUrlParameters(array_merge([
            'gateway' => static::$gateway,
            'version' => static::$version,
            'merchantId' => static::$merchantId,
        ], $urlParams));

        return $client
            ->withBasicAuth('merchant.' . static::$merchantId, static::$apiPassword)
            ->{$method}($this->getUrl($action), $data);
    }

    /**
     * Create a MPGS checkout session and return the result.
     */
    public static function initiateCheckout(array $data)
    {
        /** @var self $static */
        $static = app(static::class);

        $data['apiOperation'] = 'INITIATE_CHECKOUT';
        $data['interaction']['operation'] = 'PURCHASE';

        $response = $static->execute('post', 'initiate_checkout', data: $data);

        return $response->object();
    }

    /**
     * Retrieve MPGS checkout session details
     */
    public static function retrieveSession(string $sessionId)
    {
        /** @var self $static */
        $static = app(static::class);

        $response = $static->execute('get', 'retrieve_session', urlParams: [
            'sessionId' => $sessionId,
        ]);

        return $response->object();
    }

    /**
     * Retrieve the order details from MPGS.
     */
    public static function retrieveOrder($orderId)
    {
        /** @var self $static */
        $static = app(static::class);

        $response = $static->execute('get', 'retrieve_order', urlParams: [
            'orderId' => $orderId,
        ]);

        return $response->object();
    }
}
