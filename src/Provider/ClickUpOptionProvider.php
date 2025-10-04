<?php

namespace Albertanderberg\OAuth2\Client\Provider;

use League\OAuth2\Client\OptionProvider\OptionProviderInterface;
use League\OAuth2\Client\Provider\AbstractProvider;

class ClickUpOptionProvider implements OptionProviderInterface
{

    /**
     * @inheritdoc
     */
    public function getAccessTokenOptions($method, array $params)
    {
        $options = ['headers' => ['content-type' => 'application/json']];

        if ($method === AbstractProvider::METHOD_POST) {
            $options['body'] = json_encode($params);
        }

        return $options;
    }
}