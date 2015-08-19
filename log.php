<?php
class  log{
	const logfile = "./logfile.txt";

	public function writelog($str){

		$fid = fopen(self::logfile,'a+');
		if(!$fid){
		  echo "文件打开失败";
		}else{
		fwrite($fid,date('Y-m-d H:i:s',time())."\r\n");
		fwrite($fid,$str."\r\n");
		fclose($fid);

		}


	}





}







?>