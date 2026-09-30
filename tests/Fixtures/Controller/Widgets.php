<?php

namespace APP\Controller;

/**
 * Controller fixture for router dispatch tests
 */
class Widgets extends \RPC\Controller
{
    public function indexGET(): void
    {
        $this->template = 'none';
        echo 'widgets:index';
    }

    public function saveGET(): void
    {
        $this->template = 'none';
        echo 'widgets:save:get';
    }

    public function savePOST(): void
    {
        $this->template = 'none';
        echo 'widgets:save:post';
    }

    public function removeDELETE(): void
    {
        $this->template = 'none';
        echo 'widgets:remove:delete';
    }

    public function submitPOST(): void
    {
        $this->template = 'none';
        echo 'widgets:submit:post';
    }

    public function explodeGET(): void
    {
        echo 'partial output';
        throw new \RuntimeException('boom');
    }

    public function typeErrorGET(): void
    {
        $this->template = 'none';
        strlen([]);
    }

    public function goneGET(): void
    {
        throw new \RPC\Exception\HttpException('Gone for good', 410);
    }
}
