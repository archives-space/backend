<?php

namespace App\Provider;

use App\Model\ApiResponse\ApiResponse;

interface ProviderInterface
{
    /**
     * @param string $id
     * @return ApiResponse
     */
    public function findById(string $id);

    /**
     * @return ApiResponse
     */
    public function findAll();
}