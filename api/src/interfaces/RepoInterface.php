<?php
namespace Src\Interfaces;

interface RepoInterface {
    public function find($id);
    public function findAll($page);
    public function add($data);
    public function update($id, $data);
    public function delete($id);
}