<?php

namespace App\Manager;

/**
 * Interface BaseManagerInterface
 * @package App\Manager
 */
interface BaseManagerInterface
{
    public function create();
    public function edit(string $id);
    public function delete(string $id);

    public function requiredField();
}