<?php

namespace App\BusinessLogic\Repositories;

interface Repository {
  function all();
  function get($id);
  function save($entity);
  function delete($id);
}