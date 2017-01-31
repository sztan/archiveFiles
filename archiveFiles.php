<?php
$debut=microtime(true);
$log=[];
array_push($log,"Début de l'archivage: ".date('Y-m-d H:i:s',$debut));

//répertoire à archiver
$targetDir="./img";

$directory = dir($targetDir);

  function mkBckpDir($directory) { // créer le répertoire de sauvegarde
    $newDir = $directory->path.'/'.date('Ymd',time());
    if(!(file_exists($newDir))) {  // n'existe pas encore
      mkdir($newDir);              // le créer
      return [$newDir.'/1',mkdir($newDir.'/1')];
    }
    else {                         // existe déjà, ajouter un sous-rep ( ..,2,3,...)
      $d=dir($newDir);
      $v=[];                       // sous répertoires
      while (false !== ($entry = $d->read())) {
        if ($entry != '.' && $entry != '..') {
          array_push($v,$entry);
        }
      }
      $d->close();
      $l = empty($v)?0:max($v);    // $l for last file
      $n = (string)++$l;           // $n for 'next file'
      $V=$newDir.'/'.(string)$n;   // sous-rep qu'on va créer
      return [$V,mkdir($V)];       // ajoute un sous-rep
    }
  }

  $files=[];                     // recenser les fichiers à sauvegarder
  while ($entry = $directory->read()) {
    if($entry!='.' && $entry != '..' && !is_dir($directory->path.'/'.$entry) && $entry != 'archiveLog') {
      array_push($files, $entry);
    }
  }

  if (!empty($files)) {          // s'il y en a
    $do = mkBckpDir($directory);   // créer le répertoire de sauvegarde
    if (isset($do) && $do[1]) {
      $bckpDir=$do[0];
      array_push($log, "création du répertoire de sauvegarde ".$bckpDir);
    }

    if (isset($do) && $do[1]) {
        foreach($files as $file) {     // sauvegarder
          $before=microtime(true);
          $sourceFullPath = $directory->path.'/'.(string)$file;
          $destinationFullPath = $bckpDir.'/'.(string)$file;
          if(copy($sourceFullPath,$destinationFullPath)) unlink($sourceFullPath);
          $after=microtime(true);
          array_push($log,$sourceFullPath." archivé dans ".$destinationFullPath." en ".(string)($after-$before)."s.");
        }
    }
  }
  else array_push($log, "pas de fichiers à sauvegarder.");
  $fin=microtime(true);
  array_push($log,"Fin de l'archivage: ".date('Y-m-d H:i:s',$fin));
  array_push($log,"archivage effectué en ".(string)($fin-$debut).'s.');

  // création du fichier de log
  $logFile=fopen(__DIR__.substr($targetDir,1).'/archiveLog','a');
  foreach($log as $var) {
    fwrite($logFile,$var."\n");
  }
  fclose($logFile);
?>
