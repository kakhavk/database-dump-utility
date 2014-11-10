<?php

# Database Database Backup class
# Version 1.0
# Writen By Kakhaber Kashmadze <info@soft.ge>
# Licensed under MIT License

# This version is for linux server

class Backup{
	
    private $dbType; /* mysql, pgsql */
    private $backupType='gzip';
    
	function __construct(){
		;
	}
    
    /* Sets Database Type: mysql or pgsql */
    function setDbType($dbType){
        $dbtype=strtolower(trim($dbType));
        if($dbtype=='mysqli') $dbtype='mysql';
        if($dbtype=='postgresql') $dbtype='pgsql';
        
        $this->dbType=$dbtype;
    }
    
    /* Returns Database Type: mysql or pgsql */
    function retDbType(){
        return $this->dbType;
    }
    
    
    function retBackupType(){
        return $this->backupType;
    }
    
    function setBackupType($backupType){
        $this->backupType=$backupType;
    }
	
    function downFile($file, $fileNm, $ctype) {
        if (file_exists($file)) {
            if(ob_get_level()!==0) ob_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: '.$ctype.'');
            header('Content-Length: ' . filesize($file));
            header('Content-Disposition: attachment; filename=' . $fileNm);
            readfile($file);
            unlink($file);
            exit;
        }

    }
    
	function save($backupPath, $osType='linux'){
		
		$dbhost = DBHOST;
		$dbuser = DBUSER;
		$dbpass = DBPASS;
		$dbname = DBNAME;
		
        
		$backup_path=$backupPath;
		$fileName=$dbname."_".date("Y-m-d-H-i-s").".sql";		
		$backup_file=$backup_path.$fileName;
		
		if($osType=='linux'){
        
            if($this->retBackupType()=='gzip'){
                $backup_file = $backup_file. '.gz';
                
                
                
                if($this->retDbType()=='mysql')
                    $command = "mysqldump --opt -h ".$dbhost." -u ".$dbuser." -p".$dbpass." ".$dbname." | gzip > ".$backup_file;
                elseif($this->retDbType()=='pgsql'){
                    system("export PGPASSWORD=".$dbpass);
                    $command = "pg_dump -h ".$dbhost." -U ".$dbuser." ".$dbname." | gzip > ".$backup_file;
                }
                system($command);

                        
                if(fopen($backup_file, "r")){
                   $fileName=$fileName.'.gz';
                   $this->downFile($backup_file, $fileName, "application/x-gzip");               
                   return true;
                }else{
                   echo "<br />Error saving file ".$backup_file;
                }            
            }elseif($this->retBackupType()=='zip'){
                
                    if($this->retDbType()=='mysql')
                        $command = "mysqldump --opt -h ".$dbhost." -u ".$dbuser." -p".$dbpass." ".$dbname." > ".$backup_file;
                    elseif($this->retDbType()=='pgsql'){
                        system("export PGPASSWORD=".$dbpass);
                        $command = "pg_dump -h ".$dbhost." -U ".$dbuser." ".$dbname." > ".$backup_file;
                    }
                    
                    system($command);

                    $backupFileZip=$backup_file.'.zip';
                    $downloadFileName=$fileName.'.zip';
                    
                    if(fopen($backup_file, "r")){
                        
                        if(filesize($backup_file)>0){
                            
                            $zip = new ZipArchive;
                            if ($zip->open($backupFileZip, ZipArchive::CREATE) === TRUE) {
                                $zip->addFile($backup_file, $fileName);
                                $zip->close();
                                
                                unlink($backup_file);
                                $this->downFile($backupFileZip, $downloadFileName, "application/zip");
                            }else{
                                echo "\nError: size file has not created ";
                                return false;
                            }
                            

                            
                        }else{
                            echo "\nError: size of file is ".filesize($backup_file);
                            return false;
                        }
                    }else{
                       echo "\nError";
                       return false;
                    }
            }           
            
		}        
		return false;
	}

	
	
	
	
	
	
	
	
	
	
	
}