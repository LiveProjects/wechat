<?php
class  log{
	const logfile = "./logfile.txt";

	public function writelog($str){

		$fid = fopen(self::logfile,'a+');
		if(!$fid){
		  echo "�ļ���ʧ��";
		}else{
		fwrite($fid,date('Y-m-d H:i:s',time())."\r\n");
		fwrite($fid,$str."\r\n");
		fclose($fid);

		}


	}





}







?>