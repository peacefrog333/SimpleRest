<?php
namespace App\Repositories;

interface BaseRepositoryInterface {
    public function getAll();
    public function store($data);
    public function get($id);
    public function update($id, $data);
    public function destroy($id);
}
