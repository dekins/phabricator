<?php

final class PhabricatorRepositoryPHIDTypeRepository
  extends PhabricatorPHIDType {

  const TYPECONST = 'REPO';

  public function getTypeConstant() {
    return self::TYPECONST;
  }

  public function getTypeName() {
    return pht('Repository');
  }

  public function newObject() {
    return new PhabricatorRepository();
  }

  protected function buildQueryForObjects(
    PhabricatorObjectQuery $query,
    array $phids) {

    return id(new PhabricatorRepositoryQuery())
      ->withPHIDs($phids);
  }

  public function loadHandles(
    PhabricatorHandleQuery $query,
    array $handles,
    array $objects) {

    foreach ($handles as $phid => $handle) {
      $repository = $objects[$phid];

      $monogram = $repository->getMonogram();
      $callsign = $repository->getCallsign();
      $name = $repository->getName();

      $handle->setName($monogram);
      $handle->setFullName("{$monogram} {$name}");
      $handle->setURI("/diffusion/{$callsign}/");
      $handle->setIcon('fa-database');
    }
  }

  public function canLoadNamedObject($name) {
    return preg_match('/^r[A-Z]+$/', $name);
  }

  public function loadNamedObjects(
    PhabricatorObjectQuery $query,
    array $names) {

    $id_map = array();
    foreach ($names as $name) {
      $id = substr($name, 1);
      $id_map[$id][] = $name;
    }

    $objects = id(new PhabricatorRepositoryQuery())
      ->setViewer($query->getViewer())
      ->withCallsigns(array_keys($id_map))
      ->execute();

    $results = array();
    foreach ($objects as $object) {
      $callsign = $object->getCallsign();
      foreach (idx($id_map, $callsign, array()) as $name) {
        $results[$name] = $object;
      }
    }

    return $results;
  }

}
