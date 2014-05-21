<?php

namespace Sokil\Rest\Client\RequestMock;

class ParametricUrlRequestMock extends \Sokil\Rest\Client\Request
{
    protected $_url = '/resource/{parameter}';
}

