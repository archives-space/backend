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

    /**
     * called by init method in BaseManager file
     * @return mixed
     */
    public function setPostedObject();
}