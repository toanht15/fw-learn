<?php
/*********************************
 * MVCのCのCの中身
 * @author t.ishida
 * @cre    2008/02/24
 **********************************/
require_once 'aafwValidatorBase.php';
require_once 'aafwValidator.php';
require_once 'aafwApplicationConfig.php';
require_once 'base/aafwActionBase.php';

abstract class aafwGETActionBase extends aafwActionBase {
  public function doService( ) {
    if ( $this->POST )          return '404';

    if ( $this->ContainerName && $form = $this->SESSION['ActionContainer'][$this->ContainerName]['ValidateError'] ) {
      foreach ( $form as $key => $value ) $this->Data['ActionForm'][$key] = $value;
    }
    elseif ( $this->ContainerName && $form = $this->SESSION['ActionContainer'][$this->ContainerName]['Result'] ) {
      foreach ( $form as $key => $value ) $this->Data['ActionForm'][$key] = $value;
    }

    if ( $this->ContainerName && $form = $this->SESSION['ActionContainer'][$this->ContainerName]['Errors'] ) {
      $this->Data['ActionError'] =  $this->SESSION['ActionContainer'][$this->ContainerName]['Errors'] ;
    }
    $ret = $this->doAction();

    if ( is_array ( $this->Data ) && $form = $this->GET ) {
      
      foreach ( $form as $key => $value ) $this->Data['ActionForm'][$key] = $value;
    }

    if ( $this->ContainerName && $form = $this->SESSION['ActionContainer'][$this->ContainerName]['Result'] ) {
      foreach ( $form as $key => $value ) $this->Data['ActionForm'][$key] = $value;
    }

    if ( $this->ContainerName && $form = $this->SESSION['ActionContainer'][$this->ContainerName]['ValidateError'] ) {
      foreach ( $form as $key => $value ) $this->Data['ActionForm'][$key] = $value;
    }
    if ( $this->ContainerName && $form = $this->SESSION['ActionContainer'][$this->ContainerName]['Errors'] ) {
      $this->Data['ActionError'] =  $this->SESSION['ActionContainer'][$this->ContainerName]['Errors'] ;
      $this->SESSION['ActionContainer'][$this->ContainerName]['Errors'] = null;
    }
    return $ret;
  }
  abstract function doAction ();
}
