<?php

class wip_now{

	function __construct() {
		require_once(ROOT.'connect.php');   
	}

	
	function wip_stacked_time($process,$station,$period){

		$conn = new create_conn;
		$conn->conn('wip');
		
		//多站別,x軸為日期
		if($station=='0' && $period=='day'){
			$table = "abc";
			$table = $process."_history";
			$sql = "SELECT date,sum(`OUTPUT`) as OUTPUT,MO,作業站,進站時間 from `$table` WHERE 下線類別<>'' group by MO,作業站,date ORDER BY sn DESC";

		//多站別,x軸為站別
		}else if($station=='0' && $period=='now'){

			if($process=="FE"){

				$sql = "SELECT SUBLOT,sum(`片數`) as OUTPUT,作業站,`進站時間(hr)` from `gr_new` WHERE (RIGHT(`作業站`,3) between '007' and '060') AND 下線類別<>'' group by SUBLOT,作業站 
						union all
						SELECT SUBLOT,sum(`片數`) as OUTPUT,作業站,`進站時間(hr)` from `grds_new` WHERE (RIGHT(`作業站`,3) between '007' and '060') AND  下線類別<>'' group by SUBLOT,作業站
						union all					
						SELECT SUBLOT,sum(`片數`) as OUTPUT,作業站,`進站時間(hr)` from `ds_new` WHERE (RIGHT(`作業站`,3) between '007' and '060') AND  下線類別<>'' group by SUBLOT,作業站	
						union all					
						SELECT SUBLOT,sum(`片數`) as OUTPUT,作業站,`進站時間(hr)` from `rw_new` WHERE (RIGHT(`作業站`,3) between '007' and '060') AND  下線類別<>'' group by SUBLOT,作業站									
						";

			}else if($process=="BE"){

				$sql = "SELECT SUBLOT,sum(`片數`) as OUTPUT,作業站,`進站時間(hr)` from `gr_new` WHERE (RIGHT(`作業站`,3) between '061' and '120') AND 下線類別<>'' group by SUBLOT,作業站 
						union all
						SELECT SUBLOT,sum(`片數`) as OUTPUT,作業站,`進站時間(hr)` from `grds_new` WHERE (RIGHT(`作業站`,3) between '061' and '120') AND  下線類別<>'' group by SUBLOT,作業站
						union all					
						SELECT SUBLOT,sum(`片數`) as OUTPUT,作業站,`進站時間(hr)` from `ds_new` WHERE (RIGHT(`作業站`,3) between '061' and '120') AND  下線類別<>'' group by SUBLOT,作業站	
						union all					
						SELECT SUBLOT,sum(`片數`) as OUTPUT,作業站,`進站時間(hr)` from `rw_new` WHERE (RIGHT(`作業站`,3) between '061' and '120') AND  下線類別<>'' group by SUBLOT,作業站									
						";

			}


		//單站別,x軸為日期
		}else if($station<>'0' && $period=='day'){

			$table = "all_history";
			$sql = "SELECT date,片數,SUBLOT,作業站,`進站時間(hr)` from `$table` use index(type_station) WHERE 下線類別<>'' AND 作業站='$station' group by SUBLOT,date ORDER BY sn DESC";

			//製程段,x軸為日期
		}else if($station<>'0' && $period=='day'){

			$sql = "SELECT date,sum(`OUTPUT`) as OUTPUT,MO,作業站,進站時間 from `rw_history` WHERE 下線類別<>'' AND 作業站='$station' group by MO,date
					union all
					SELECT date,sum(`OUTPUT`) as OUTPUT,MO,作業站,進站時間 from `gr_history` WHERE 下線類別<>'' AND 作業站='$station' group by MO,date
					union all
					SELECT date,sum(`OUTPUT`) as OUTPUT,MO,作業站,進站時間 from `grds_history` WHERE 下線類別<>'' AND 作業站='$station' group by MO,date
					union all
					SELECT date,sum(`OUTPUT`) as OUTPUT,MO,作業站,進站時間 from `dsw_history` WHERE 下線類別<>'' AND 作業站='$station' group by MO,date ORDER BY sn DESC										
					";

		}

		$conn->result($sql);

		$output=[];
		$mo=[];
		$station=[];
		$time=[];
		$date=[];

		while($conn->get_row()){

			
			if(isset($conn->row['date'])){

				if(strpos($conn->row['date'],"07:00")!=false){

					$mo[] = $conn->row['SUBLOT'];
					$output[] = $conn->row['片數'];
					$station[] = $conn->row['作業站'];
					$time[] = $conn->row['進站時間(hr)'];
					$date[] = $conn->row['date'];
					
				}

			}else{

				$mo[] = $conn->row['SUBLOT'];
				$output[] = $conn->row['OUTPUT'];
				$station[] = $conn->row['作業站'];
				$time[] = $conn->row['進站時間(hr)'];
			

			}
		}

		$ans = array(
			"mo"=>$mo,
			"output"=>$output,
			"station"=>$station,
			"time"=>$time,
			"date"=>$date
		);

		return $ans;
		

	}
	//for 找hold mo的詳細資訊
	function  wip_hold_ratio_all(){
		$conn = new create_conn;
		$conn->conn('wip');

			$sql = "SELECT DEVICE,作業站,OUTPUT from `rw_now_hold` where MO='$mo'
					union all
					SELECT DEVICE,作業站,OUTPUT from `gr_now_hold` where MO='$mo'
					union all
					SELECT DEVICE,作業站,OUTPUT from `ds_now_hold` where MO='$mo'";
			$conn->result($sql);

			$output=[];
			while($conn->get_row()){

				$device = $conn->row['DEVICE'];				
				$作業站 = $conn->row['作業站'];
				$output[] = $conn->row['OUTPUT'];

			}			

				$output = array_sum($output);

				$ans = array(
							"device"=>$device,
							"作業站"=>$作業站,
							"output"=>$output
				);

				return $ans;

	}
	//for 找hold的mo數
	function wip_hold_mo($device,$客戶版本){

		$conn = new create_conn;
		$conn->conn('wip');

		//傳來的device如果是MO 就用MO找數量

		if(strpos($device,"OLT")>0){

			$sql = "SELECT OUTPUT from `rw_now` where MO='$device'
					union all
					SELECT OUTPUT from `gr_now` where MO='$device'
					union all
					SELECT OUTPUT from `ds_now` where MO='$device'";
			$conn->result($sql);

			$output=[];
			while($conn->get_row()){

				$output[] = $conn->row['OUTPUT'];				

			}
			$output = array_sum($output);
			return $output;

		}else{


				if($客戶版本=="ALL"){

					$sql = "SELECT MO from `rw_now_hold` where DEVICE='$device' or 客戶群組='$device'
							union all
							SELECT MO from `gr_now_hold` where DEVICE='$device' or 客戶群組='$device'
							union all
							SELECT MO from `ds_now_hold` where DEVICE='$device' or 客戶群組='$device'";

				}else{

					$sql = "SELECT MO from `rw_now_hold` where DEVICE='$device' and 客戶版本='$客戶版本'
							union all
							SELECT MO from `gr_now_hold` where DEVICE='$device' and 客戶版本='$客戶版本'
							union all
							SELECT MO from `ds_now_hold` where DEVICE='$device' and 客戶版本='$客戶版本'";

				}
				$conn->result($sql);
				$mo=[];

				while($conn->get_row()){

					$mo[] = $conn->row['MO'];
					
				}

				$mo = array_values(array_unique($mo));

				$output=[];

				for($i=0;$i<count($mo);$i++){

					$sql = "SELECT OUTPUT from `rw_now` where MO='$mo[$i]'";
					$conn->result($sql);

					while($conn->get_row()){

						$output[] = $conn->row['OUTPUT'];				

					}
				
				}
				$output = array_sum($output);
				return $output;
		}

		

	}

	//for status_chart
	function wip_hold_ratio($process,$option,$date_val){

		$conn = new create_conn;

	if($date_val=="NOW"){
		
					if($process=="CP"){						
						$sql = "SELECT status,date,作業站,sum(`output`) as output FROM `cp_now` WHERE 1 group by 作業站,status";		

					}else if($process=="ALL"){

					$sql = "SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 1 group by 作業站,status
							union all
							SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 1 group by 作業站,status
							union all
							SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 1 group by 作業站,status";		

					}else if($process=="GR"){

						$sql = "SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 1 group by 作業站,status
								union all
								SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 1 group by 作業站,status";	

					}else if($process=="DS"){

						$sql = "SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 1 group by 作業站,status
								union all
								SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 1 group by 作業站,status";	

					}else if($process=="RW"){

						$sql = "SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE (RIGHT(`作業站`,3) between '061' and '111') group by 作業站,status
								union all
								SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE (RIGHT(`作業站`,3) between '061' and '111') group by 作業站,status
								union all
								SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE (RIGHT(`作業站`,3) between '061' and '111') group by 作業站,status";


					}else if($process=="FE"){

						$sql = "SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE (RIGHT(`作業站`,3) between '007' and '060') group by 作業站,status
								union all
								SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE (RIGHT(`作業站`,3) between '007' and '060') group by 作業站,status
								union all
								SELECT status,date,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE (RIGHT(`作業站`,3) between '007' and '060') group by 作業站,status";	

					}

		}else if($process<>"ALL" && $date_val<>"NOW"){			

				if($process=="S059"){
					$table ='ds_history';
				}else if($process=="S060"){
					$table = 'ds_history';
				}else if($process=="S081"){
					$table = 'rw_history';		
				}else if($process=="S093" || $process=="S097"){
					$table = 'rw_history';
				}else if($process=="S020"){
					$table = 'gr_history';	
				}else{
				$table = $process."_history";
				}

				if($process=="CP"){
					
					if($date_val=="ALL"){

						$sql = "SELECT status,date,作業站,sum(`output`) as output FROM `cp_history` WHERE 1 group by date,status";
				

					}else{
						$sql = "SELECT status,date,作業站,sum(`output`) as output FROM `cp_history` WHERE date ='$date_val' group by 作業站,status";						
					}

				}else{

					switch($option){

						case 'byday':


							if(strpos($process,"S")!=false){
								
								if($process=="S093"){

									$sql = "SELECT status,date,作業站,sum(`output`) as output FROM `$table` WHERE 作業站='$process' or 作業站='S090' group by date,status";

									}else{

									$sql = "SELECT status,date,作業站,sum(`output`) as output FROM `$table` WHERE 作業站='$process' group by date,status";
									}	
								}else{

									$sql = "SELECT status,作業站,date,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `$table` WHERE 1 group by date,status";											
								}							

						break;

						case 'bystation':
							$sql = "SELECT status,作業站,date,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `$table` WHERE date ='$date_val' group by 作業站,status";		
						break;

					}
				}

		}else if($process=="ALL" && $date_val<>"NOW"){

			switch($option){
				case 'byday':
					$sql = "SELECT status,作業站,date,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_history` WHERE 1 group by date,status
							union all
							SELECT status,作業站,date,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_history` WHERE 1 group by date,status
							union all
							SELECT status,作業站,date,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_history` WHERE 1 group by date,status";		
				break;

				case 'bystation':
					$sql = "SELECT status,作業站,date,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_history` WHERE date ='$date_val' group by 作業站,status
							union all
							SELECT status,作業站,date,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_history` WHERE date ='$date_val' group by 作業站,status
							union all
							SELECT status,作業站,date,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_history` WHERE date ='$date_val' group by 作業站,status";		
				break;
			}


		}
	

		$conn->conn('wip');
		$conn->result($sql);
								
			$output_run=[];
			$output_queue=[];			
			$date=[];
			$output_hold=[];
			$output=[];
			$station=[];

			$i=0;

			while ($conn->get_row()) {

				$output[$i] = $conn->row['output'];    
				$date[$i] = $conn->row['date'];
				$station[$i] = $conn->row['作業站'];
				$status = $conn->row['status'];

				if($status=="HOLD" || $status=="MRB"){

					$output_hold[$i] = $conn->row['output'];
					$output_run[$i] = 0;
					$output_queue[$i] = 0;

				}else if($status=="RUN"){

					$output_run[$i] = $conn->row['output'];
					$output_queue[$i] = 0;
					$output_hold[$i] = 0;

				}else if($status=="QUEUE"){

					$output_queue[$i] = $conn->row['output'];
					$output_run[$i] = 0;
					$output_hold[$i] = 0;

				}else{

					$output_run[$i] = 0;
					$output_queue[$i] = 0;
					$output_hold[$i] = 0;

				}

				$i++;


				
			}				

			

			$wip_ans=array(

							"output"=>$output,
							"date"=>$date,
							"output_hold"=>$output_hold,
							"output_run"=>$output_run,
							"output_queue"=>$output_queue,
							"station"=>$station

							);									

			return $wip_ans;
			


	}

	function wip_new_update_log(){

		$conn = new create_conn;
		$sql = "SELECT `log` FROM `wip_new_update_log` where 1 order by sn DESC limit 1";
		$conn->conn('wip');
		$conn->result($sql);
		while($conn->get_row()) {
			$log = $conn->row['log'];
			return $log;
		}
	}
	function week_range($vendor){

			$conn = new create_conn;
		
			switch($vendor){
				
				case 'CP' :
				
					$sql = "SELECT 同欣週 FROM `cp_now` WHERE 1 group by DEVICE,客戶版本,作業站
							";
							
						$conn->conn('wip');
						$conn->result($sql);

							

						$同欣週=[];
						
						while ($conn->get_row()) {

    
							$同欣週[] = $conn->row['同欣週'];
					
					
						}				
						
						
				
				break;
					
				case '小客戶' :
				
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' group by DEVICE,客戶版本,作業站
										";		
							
							$conn->conn('wip');
							$conn->result($sql);
							$同欣週=[];
						
						while($conn->get_row()) {	
					

							$同欣週[] = $conn->row['同欣週'];
										
						}										
						
					
				break;		

				case 'ALL' :
				
					$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 1 group by 作業站
							union all
							SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 1 group by 作業站
							union all
							SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 1 group by 作業站						
							union all
							SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 1 group by 作業站
							";		
				
					$conn->conn('wip');
					$conn->result($sql);							

					$同欣週=[];
						
					while ($conn->get_row()) {


						$同欣週[] = $conn->row['同欣週'];
					
				
					}										
						
				
				break;						


				default :
				
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 ='$vendor' group by DEVICE,客戶版本,作業站
										union all
								
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 ='$vendor' group by DEVICE,客戶版本,作業站
										union all
				
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 ='$vendor' group by DEVICE,客戶版本,作業站						
										union all
							
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 ='$vendor' group by DEVICE,客戶版本,作業站

										";	
				
					
								$conn->conn('wip');
								$conn->result($sql);

								$同欣週=[];
								
								while ($conn->get_row()) {

			
									$同欣週[] = $conn->row['同欣週'];
					
					
								}										
						
					
								
								
			}
			
			$同欣週 = array_values(array_unique($同欣週));
			return $同欣週;
	}

	function wip_select_hold($process,$device,$作業站,$客戶群組){

		$conn = new create_conn;
		//$process為客戶群組

		if($process=="CP"){

				if($作業站=="TOTAL" && $device<>"TOTAL"){

					$sql = "SELECT * from cp_hold where DEVICE ='$device'";				

				}else if($作業站<>"TOTAL" && $device<>"TOTAL"){

					$sql = "SELECT * from cp_hold where DEVICE ='$device' and 作業站='$作業站'";					

				}else if($作業站<>"TOTAL" && $device=="TOTAL"){

					$sql = "SELECT * from cp_hold where 作業站 ='$作業站'";				

				}else if($device=="TOTAL" && $作業站=="TOTAL"){

					$sql = "SELECT * from cp_hold where 1";		

				}
		}else{


				if($process=="TOTAL" && $作業站=="TOTAL" && $device<>"TOTAL"){

					$sql = "SELECT * from ds_hold where DEVICE ='$device'
					UNION ALL
					SELECT * from gr_hold where DEVICE ='$device'
					UNION ALL
					SELECT * from grds_hold where DEVICE ='$device'					
					UNION ALL
					SELECT * from rw_hold where DEVICE ='$device'					
					";				

				}else if($process=="TOTAL" && $作業站<>"TOTAL" && $device<>"TOTAL"){

					$sql = "SELECT * from ds_hold where DEVICE ='$device' and 作業站='$作業站'
					UNION ALL
					SELECT * from gr_hold where DEVICE ='$device' and 作業站='$作業站'
					UNION ALL
					SELECT * from grds_hold where DEVICE ='$device' and 作業站='$作業站'
					UNION ALL
					SELECT * from rw_hold where DEVICE ='$device' and 作業站='$作業站'
					";					

				}else if($process<>"TOTAL" && $作業站=="TOTAL" && $device<>"TOTAL"){

					$sql = "SELECT * from ds_hold where DEVICE ='$device' and 料品編號 like '%$process%'
					UNION ALL
					SELECT * from gr_hold where DEVICE ='$device' and 料品編號 like '%$process%'
					UNION ALL
					SELECT * from grds_hold where DEVICE ='$device' and 料品編號 like '%$process%'
					UNION ALL
					SELECT * from rw_hold where DEVICE ='$device' and 料品編號 like '%$process%'
					";				

				}else if($process=="TOTAL" && $device=="TOTAL" && $作業站<>"TOTAL"){

					$sql = "SELECT * from ds_hold where 作業站 = '$作業站'
					UNION ALL
					SELECT * from gr_hold where 作業站 = '$作業站'
					UNION ALL
					SELECT * from grds_hold where 作業站 = '$作業站'					
					UNION ALL
					SELECT * from rw_hold where 作業站 = '$作業站'					
					";		

				}else if($process<>"TOTAL" && $device=="TOTAL" && $作業站<>"TOTAL"){

					$sql = "SELECT * from ds_hold where 作業站 = '$作業站' and 料品編號 like '%$process%'
					UNION ALL
					SELECT * from gr_hold where 作業站 = '$作業站' and 料品編號 like '%$process%'
					UNION ALL
					SELECT * from grds_hold where 作業站 = '$作業站' and 料品編號 like '%$process%'					
					UNION ALL
					SELECT * from rw_hold where 作業站 = '$作業站' and 料品編號 like '%$process%'					
					";					

				}else if($device=="TOTAL" && $作業站=="TOTAL" && $process<>"TOTAL"){

					$sql = "SELECT * from ds_hold where 料品編號 like '%$process%'
					UNION ALL
					SELECT * from gr_hold where 料品編號 like '%$process%'
					UNION ALL
					SELECT * from grds_hold where 料品編號 like '%$process%'					
					UNION ALL
					SELECT * from rw_hold where 料品編號 like '%$process%'					
					";					

				}else{

						$sql = "SELECT * from ds_hold where 1
								UNION ALL
								SELECT * from gr_hold where 1
								UNION ALL
								SELECT * from grds_hold where 1					
								UNION ALL
								SELECT * from rw_hold where 1					
								";			
					
				}
			}	
		$conn->conn('wip');
		$conn->result($sql);
					
					$device = [];
					$客戶版本 = [];
					$下線類別 = [];
					$sublot = [];
					$同欣週 = [];
					$片數 = [];
					$status_time = [];
					$hold_code = [];
					$hold_code_explain = [];
					$異常原因 = [];
					$主因1 = [];
					$主因2 = [];
					$料品編號 = [];
					$優先等級 = [];
					$指定BIN = [];
					$GrossDIE = [];
					$FrameDIE = [];

				while ($conn->get_row()) {
					
					//$device = $row["DEVICE"];			
					$device[] = $conn->row["DEVICE"];
					$客戶版本[] = $conn->row["客戶版本"];
					$下線類別[] = $conn->row["下線類別"];
					$sublot[] = $conn->row["SUBLOT"];
					$同欣週[] = $conn->row["同欣週"];
					$片數[] = $conn->row["片數"];
				//	$作業站 = $conn->row["作業站"];
					$status_time[] = $conn->row["STATUS_TIME"];
					$hold_code[] = $conn->row["HOLD CODE"];
					$hold_code_explain[] = $conn->row["HOLD CODE說明"];
					$異常原因[] = $conn->row["異常原因"];
					$主因1[] = $conn->row["主因1"];
					$主因2[] = $conn->row["主因2"];
					$料品編號[] = $conn->row["料品編號"];
					$優先等級[] = $conn->row["優先等級"];
					$指定BIN[] = $conn->row["指定BIN"];
					$GrossDIE[] = $conn->row["Gross Die"];
					$FrameDIE[] = $conn->row["Frame Die"];

				}			
		
			
											
					$ans=array(				
						"device"=>$device,
						"客戶版本"=>$客戶版本,
						"下線類別"=>$下線類別,
						"sublot"=>$sublot,
						"同欣週"=>$同欣週,
						"片數"=>$片數,				
						"status_time"=>$status_time,
						"hold_code"=>$hold_code,
						"hold_code_explain"=>$hold_code_explain,
						"異常原因"=>$異常原因,
						"主因1"=>$主因1,
						"主因2"=>$主因2,
						"料品編號"=>$料品編號,
						"優先等級"=>$優先等級,
						"指定BIN"=>$指定BIN,
						"GrossDIE"=>$GrossDIE,
						"FrameDIE"=>$FrameDIE				
					);
					
					return $ans;

	}

    function wip_check($vendor,$week,$mo){

			//vendor=廠商
			//week=週數
			//mo = MO

			$conn = new create_conn;		

			switch($vendor){
				
				case 'CP' :
				
				    //NOW
					
					if($week=="ALL" & $mo=="ALL"){
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now` WHERE 1 group by DEVICE,客戶版本,作業站
							";
					}else if($week<>"ALL" & $mo=="ALL"){
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now` WHERE `同欣週` = '$week' group by DEVICE,客戶版本,作業站
							";
					}else if($week=="ALL" & $mo<>"ALL"){
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now` WHERE `MO` = '$mo' group by DEVICE,客戶版本,作業站
							";
					}else if($week<>"ALL" & $mo<>"ALL"){
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now` WHERE `MO` = '$mo' and 同欣週 ='$week' group by DEVICE,客戶版本,作業站
							";
					}
					
					//	UNION ALL
					//					SELECT DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_pass` WHERE device<>'' group by DEVICE,客戶版本,作業站								
					$conn->conn('wip');
					$conn->result($sql);

					
						$客戶群組=[];
						$device=[];
						$客戶版本=[];
						$作業站=[];
						$同欣週=[];

						$output=[];
						
						while ($conn->get_row()) {

							$客戶群組[] = $conn->row['客戶群組'];							
							$device[] = $conn->row['DEVICE'];
							$客戶版本[] = $conn->row['客戶版本'];
							$作業站[] = $conn->row['作業站'];                                    						
							$output[] = $conn->row['output'];     
							$同欣週[] = $conn->row['同欣週'];
					
					
						}				
						

					
				//HOLD
				
					if($week=="ALL" & $mo=="ALL"){
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_hold` WHERE 1 group by DEVICE,客戶版本,作業站
							";
					}else if($week<>"ALL" & $mo=="ALL"){
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_hold` WHERE `同欣週` = '$week' group by DEVICE,客戶版本,作業站
							";
					}else if($week=="ALL" & $mo<>"ALL"){
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_hold` WHERE `MO` = '$mo' group by DEVICE,客戶版本,作業站
							";
					}else if($week<>"ALL" & $mo<>"ALL"){
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_hold` WHERE `MO` = '$mo' and 同欣週 ='$week' group by DEVICE,客戶版本,作業站
							";
					}
					

														
					$conn->result($sql);			
					
					
						$客戶群組_hold=[];
						$device_hold=[];
						$客戶版本_hold=[];
						$作業站_hold=[];
						$output_hold=[];
						
						while ($conn->get_row()) {

							$客戶群組_hold[] = $conn->row['客戶群組'];
							$device_hold[] = $conn->row['DEVICE'];
							$客戶版本_hold[] = $conn->row['客戶版本'];
							$作業站_hold[] = $conn->row['作業站'];                                    						
							$output_hold[] = $conn->row['output'];     
					
						}				
						
						
									
					
					if(isset($客戶群組) && isset($客戶群組_hold)){
					
						$wip_ans=array(
										"客戶群組"=>$客戶群組,
										"device"=>$device,
										"客戶版本"=>$客戶版本,
										"作業站"=>$作業站,
										"output"=>$output,
										"客戶群組_hold"=>$客戶群組_hold,
										"device_hold"=>$device_hold,
										"客戶版本_hold"=>$客戶版本_hold,
										"作業站_hold"=>$作業站_hold,
										"output_hold"=>$output_hold									
										
										);
									   
						return $wip_ans;
					
					}else if(isset($客戶群組) && isset($客戶群組_hold)==false){
									

						
						$wip_ans=array(
										"客戶群組"=>$客戶群組,
										"device"=>$device,
										"客戶版本"=>$客戶版本,
										"作業站"=>$作業站,
										"output"=>$output,
										"客戶群組_hold"=>[],
										"device_hold"=>[],
										"客戶版本_hold"=>[],
										"作業站_hold"=>[],
										"output_hold"=>[]									
										
										);						
						
						return $wip_ans;
						
					}					

				break;
				

				case 'CP_by_lot' :
		

					if($week=="ALL" & $mo=="ALL"){
						
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_by_lot` WHERE 1 group by DEVICE,客戶版本,作業站";
					
					}else if($week<>"ALL" & $mo=="ALL"){
						
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_by_lot` WHERE 同欣週 = '$week' group by DEVICE,客戶版本,作業站";
					
					}else if($week=="ALL" & $mo<>"ALL"){
						
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_by_lot` WHERE MO = '$mo' group by DEVICE,客戶版本,作業站";
					
					}else if($week<>"ALL" & $mo<>"ALL"){
						
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_by_lot` WHERE 同欣週 = '$week' and MO = '$mo' group by DEVICE,客戶版本,作業站";
					}
					
					$conn->conn('wip');
					$conn->result($sql);
				
						$客戶群組=[];
						$device=[];
						$客戶版本=[];
						$作業站=[];
						$output=[];
						$同欣週=[];
						
						while ($conn->get_row()) {

							$客戶群組[] = $conn->row['客戶群組'];
							$device[] = $conn->row['DEVICE'];
							$客戶版本[] = $conn->row['客戶版本'];
							$作業站[] = $conn->row['作業站'];                                    						
							$output[] = $conn->row['output'];       
							$同欣週[] = $conn->row['同欣週'];
						}				
						
						
				
					
					/* HOLD */	
					
					$sql = "SELECT DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_hold_by_lot` WHERE 1 group by DEVICE,客戶版本,作業站";
														
					$conn->result($sql);
					
					
						$客戶群組_hold=[];
						$device_hold=[];
						$客戶版本_hold=[];
						$作業站_hold=[];

						$output_hold=[];
						
						while ($conn->get_row()) {

							$客戶群組_hold[] = $conn->row['客戶群組'];
							$device_hold[] = $conn->row['DEVICE'];
							$客戶版本_hold[] = $conn->row['客戶版本'];
							$作業站_hold[] = $conn->row['作業站'];                                    						
							$output_hold[] = $conn->row['output'];              			
						}				
						

					
					$wip_ans=array(
									"客戶群組"=>$客戶群組,
									"device"=>$device,
									"客戶版本"=>$客戶版本,
									"作業站"=>$作業站,
									"output"=>$output,
									"客戶群組_hold"=>$客戶群組_hold,
									"device_hold"=>$device_hold,
									"客戶版本_hold"=>$客戶版本_hold,
									"作業站_hold"=>$作業站_hold,
									"output_hold"=>$output_hold											
									
									);
								   
					return $wip_ans;

				break;


///////////			


				case '小客戶' :

					if($week=="ALL" & $mo=="ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' group by DEVICE,客戶版本,作業站
										";		
					
					}else if($week<>"ALL" & $mo=="ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' and 同欣週 ='$week' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and 同欣週 ='$week' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and 同欣週 ='$week' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and 同欣週 ='$week' group by DEVICE,客戶版本,作業站
										";		
					
					}else if($week=="ALL" & $mo<>"ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' and MO ='$mo' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and MO ='$mo' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and MO ='$mo' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and MO ='$mo' group by DEVICE,客戶版本,作業站
										";		
					
					}else if($week<>"ALL" & $mo<>"ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' and 同欣週 ='$week' and MO ='$mo' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and 同欣週 ='$week' and MO ='$mo' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and 同欣週 ='$week' and MO ='$mo' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and 同欣週 ='$week' and MO ='$mo' group by DEVICE,客戶版本,作業站
										";		
					}
					
								

								$conn->conn('wip');
								$conn->result($sql);
								

									
									$客戶群組=[];
									$device=[];
									$客戶版本=[];
									$作業站=[];
									$output=[];
									$GrossDIE=[];
									$FrameDIE=[];
									$TT_GrossDIE=[];
									$TT_FrameDIE=[];
									$同欣週=[];

									while ($conn->get_row()) {

										$客戶群組[] = $conn->row['客戶群組'];
										$device[] = $conn->row['DEVICE'];
										$客戶版本[] = $conn->row['客戶版本'];
										$作業站[] = $conn->row['作業站'];                                    						
										$output[] = $conn->row['output'];              			
										$GrossDIE[] = $conn->row['GrossDIE'];
										$FrameDIE[] = $conn->row['FrameDIE'];
										$TT_GrossDIE[] = $conn->row['TT_GrossDIE'];
										$TT_FrameDIE[] = $conn->row['TT_FrameDIE'];						
										$同欣週[] = $conn->row['同欣週'];										
										
									}				
									
									
								
					if($week=="ALL" & $mo=="ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' group by DEVICE,客戶版本,作業站
										union all										
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' group by DEVICE,客戶版本,作業站
										";		
					
					}else if($week<>"ALL" & $mo=="ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站
										union all										
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站
										";			
					
					}else if($week=="ALL" & $mo<>"ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' and MO = '$mo' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and MO = '$mo' group by DEVICE,客戶版本,作業站
										union all										
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and MO = '$mo' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and MO = '$mo' group by DEVICE,客戶版本,作業站
										";			
					
					}else if($week<>"ALL" & $mo<>"ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' and MO = '$mo' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and MO = '$mo' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站
										union all										
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and MO = '$mo' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' and MO = '$mo' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站
										";			
							
					}
					

								$conn->result($sql);
																	
									$客戶群組_hold=[];
									$device_hold=[];
									$客戶版本_hold=[];
									$作業站_hold=[];
									$output_hold=[];
									$GrossDIE_hold=[];
									$FrameDIE_hold=[];
									$TT_GrossDIE_hold=[];
									$TT_FrameDIE_hold=[];

									while ($conn->get_row()) {

										$客戶群組_hold[] = $conn->row['客戶群組'];
										$device_hold[] = $conn->row['DEVICE'];
										$客戶版本_hold[] = $conn->row['客戶版本'];
										$作業站_hold[] = $conn->row['作業站'];                                    						
										$output_hold[] = $conn->row['output'];              			
										$GrossDIE_hold[] = $conn->row['GrossDIE'];
										$FrameDIE_hold[] = $conn->row['FrameDIE'];
										$TT_GrossDIE_hold[] = $conn->row['TT_GrossDIE'];
										$TT_FrameDIE_hold[] = $conn->row['TT_FrameDIE'];						
										
									}				
									
									
																
				/* HOLD段 */
								
								if(isset($客戶群組) && isset($客戶群組_hold)){
								
									$wip_ans=array(
													"客戶群組"=>$客戶群組,
													"device"=>$device,
													"客戶版本"=>$客戶版本,
													"作業站"=>$作業站,
													"output"=>$output,
													"GrossDIE"=>$GrossDIE,
													"FrameDIE"=>$FrameDIE,
													"TT_GrossDIE"=>$TT_GrossDIE,
													"TT_FrameDIE"=>$TT_FrameDIE,
													"客戶群組_hold"=>$客戶群組_hold,
													"device_hold"=>$device_hold,
													"客戶版本_hold"=>$客戶版本_hold,
													"作業站_hold"=>$作業站_hold,
													"output_hold"=>$output_hold,
													"GrossDIE_hold"=>$GrossDIE_hold,
													"FrameDIE_hold"=>$FrameDIE_hold,
													"TT_GrossDIE_hold"=>$TT_GrossDIE_hold,
													"TT_FrameDIE_hold"=>$TT_FrameDIE_hold														
													);									

									return $wip_ans;
									
								}else if(isset($客戶群組) && isset($客戶群組_hold)==false){		
								
									$wip_ans=array(
													"客戶群組"=>$客戶群組,
													"device"=>$device,
													"客戶版本"=>$客戶版本,
													"作業站"=>$作業站,
													"output"=>$output,
													"GrossDIE"=>$GrossDIE,
													"FrameDIE"=>$FrameDIE,
													"TT_GrossDIE"=>[],
													"TT_FrameDIE"=>[],
													"客戶群組_hold"=>[],
													"device_hold"=>[],
													"客戶版本_hold"=>[],
													"作業站_hold"=>[],
													"output_hold"=>[],
													"GrossDIE_hold"=>[],
													"FrameDIE_hold"=>[],
													"TT_GrossDIE_hold"=>[],
													"TT_FrameDIE_hold"=>[]														
													);									

									return $wip_ans;

								}									

			break;

///////////			


case 'ALL' :

	
	if($week=="ALL" & $mo=="ALL"){
		
				$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE device<>'' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE device<>'' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE device<>'' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE device<>'' group by 作業站,客戶群組
						";		
	
	}else if($week<>"ALL" & $mo=="ALL"){
		
				$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE device<>'' and 同欣週 ='$week' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE device<>'' and 同欣週 ='$week' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE device<>'' and 同欣週 ='$week' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE device<>'' and 同欣週 ='$week' group by 作業站,客戶群組
						";		
	
	}else if($week=="ALL" & $mo<>"ALL"){
		
				$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE device<>'' and MO ='$mo' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE device<>'' and MO ='$mo' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE device<>'' and MO ='$mo' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE device<>'' and MO ='$mo' group by 作業站,客戶群組
						";		
	
	}else if($week<>"ALL" & $mo<>"ALL"){
		
				$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE device<>'' and 同欣週 ='$week' and MO ='$mo' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE device<>'' and 同欣週 ='$week' and MO ='$mo' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE device<>'' and 同欣週 ='$week' and MO ='$mo' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE device<>'' and 同欣週 ='$week' and MO ='$mo' group by 作業站,客戶群組
						";		
	}
	
	

				$conn->conn('wip');
				$conn->result($sql);
				
					
					$客戶群組=[];
					$device=[];
					$客戶版本=[];
					$作業站=[];
					$output=[];
					$GrossDIE=[];
					$FrameDIE=[];
					$TT_GrossDIE=[];
					$TT_FrameDIE=[];
					$同欣週=[];

					while ($conn->get_row()) {

						$客戶群組[] = $conn->row['客戶群組'];
						$device[] = $conn->row['DEVICE'];
						$客戶版本[] = $conn->row['客戶版本'];
						$作業站[] = $conn->row['作業站'];                                    						
						$output[] = $conn->row['output'];              			
						$GrossDIE[] = $conn->row['GrossDIE'];
						$FrameDIE[] = $conn->row['FrameDIE'];
						$TT_GrossDIE[] = $conn->row['TT_GrossDIE'];
						$TT_FrameDIE[] = $conn->row['TT_FrameDIE'];						
						$同欣週[] = $conn->row['同欣週'];										
						
					}				
					
					
			
				
	if($week=="ALL" & $mo=="ALL"){
		
				$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 1 group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 1 group by 作業站,客戶群組
						union all										
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 1 group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 1 group by 作業站,客戶群組
						";		
	
	}else if($week<>"ALL" & $mo=="ALL"){
		
				$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 同欣週 = '$week' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 同欣週 = '$week' group by 作業站,客戶群組
						union all										
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 同欣週 = '$week' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 同欣週 = '$week' group by 作業站,客戶群組
						";			
	
	}else if($week=="ALL" & $mo<>"ALL"){
		
				$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE MO = '$mo' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE MO = '$mo' group by 作業站,客戶群組
						union all										
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE MO = '$mo' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE MO = '$mo' group by 作業站,客戶群組
						";			
	
	}else if($week<>"ALL" & $mo<>"ALL"){
		
				$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE MO = '$mo' and 同欣週 = '$week' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE MO = '$mo' and 同欣週 = '$week' group by 作業站,客戶群組
						union all										
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE MO = '$mo' and 同欣週 = '$week' group by 作業站,客戶群組
						union all
						SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE MO = '$mo' and 同欣週 = '$week' group by 作業站,客戶群組
						";			
			
	}
	
	
				

				$conn->result($sql);
												
					$客戶群組_hold=[];
					$device_hold=[];
					$客戶版本_hold=[];
					$作業站_hold=[];
					$output_hold=[];
					$GrossDIE_hold=[];
					$FrameDIE_hold=[];
					$TT_GrossDIE_hold=[];
					$TT_FrameDIE_hold=[];

					while ($conn->get_row()) {

						$客戶群組_hold[] = $conn->row['客戶群組'];
						$device_hold[] = $conn->row['DEVICE'];
						$客戶版本_hold[] = $conn->row['客戶版本'];
						$作業站_hold[] = $conn->row['作業站'];                                    						
						$output_hold[] = $conn->row['output'];              			
						$GrossDIE_hold[] = $conn->row['GrossDIE'];
						$FrameDIE_hold[] = $conn->row['FrameDIE'];
						$TT_GrossDIE_hold[] = $conn->row['TT_GrossDIE'];
						$TT_FrameDIE_hold[] = $conn->row['TT_FrameDIE'];						
						
					}				
					
					
											
/* HOLD段 */
				
				if(isset($客戶群組) && isset($客戶群組_hold)){
				
					$wip_ans=array(
									"客戶群組"=>$客戶群組,
									"device"=>$device,
									"客戶版本"=>$客戶版本,
									"作業站"=>$作業站,
									"output"=>$output,
									"GrossDIE"=>$GrossDIE,
									"FrameDIE"=>$FrameDIE,
									"TT_GrossDIE"=>$TT_GrossDIE,
									"TT_FrameDIE"=>$TT_FrameDIE,
									"客戶群組_hold"=>$客戶群組_hold,
									"device_hold"=>$device_hold,
									"客戶版本_hold"=>$客戶版本_hold,
									"作業站_hold"=>$作業站_hold,
									"output_hold"=>$output_hold,
									"GrossDIE_hold"=>$GrossDIE_hold,
									"FrameDIE_hold"=>$FrameDIE_hold,
									"TT_GrossDIE_hold"=>$TT_GrossDIE_hold,
									"TT_FrameDIE_hold"=>$TT_FrameDIE_hold														
									);									

					return $wip_ans;
					
				}else if(isset($客戶群組) && isset($客戶群組_hold)==false){		
				
					$wip_ans=array(
									"客戶群組"=>$客戶群組,
									"device"=>$device,
									"客戶版本"=>$客戶版本,
									"作業站"=>$作業站,
									"output"=>$output,
									"GrossDIE"=>$GrossDIE,
									"FrameDIE"=>$FrameDIE,
									"TT_GrossDIE"=>[],
									"TT_FrameDIE"=>[],
									"客戶群組_hold"=>[],
									"device_hold"=>[],
									"客戶版本_hold"=>[],
									"作業站_hold"=>[],
									"output_hold"=>[],
									"GrossDIE_hold"=>[],
									"FrameDIE_hold"=>[],
									"TT_GrossDIE_hold"=>[],
									"TT_FrameDIE_hold"=>[]														
									);									

					return $wip_ans;

				}									

break;

///////////				
///////////	
		
				default:
				
					if($week=="ALL" & $mo=="ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 ='$vendor' group by 料品編號,作業站
										union all
								
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 ='$vendor' group by 料品編號,作業站
										union all
				
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 ='$vendor' group by 料品編號,作業站						
										union all
							
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 ='$vendor' group by 料品編號,作業站

										";
					
					}else if($week<>"ALL" & $mo=="ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 ='$vendor' and 同欣週 ='$week' group by 料品編號,作業站
										union all
								
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 ='$vendor' and 同欣週 ='$week' group by 料品編號,作業站
										union all
				
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 ='$vendor' and 同欣週 ='$week' group by 料品編號,作業站						
										union all
							
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 ='$vendor' and 同欣週 ='$week' group by 料品編號,作業站

										";	
					
					}else if($week=="ALL" & $mo<>"ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 ='$vendor' and MO ='$mo' group by 料品編號,作業站
										union all
								
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 ='$vendor' and MO ='$mo' group by 料品編號,作業站
										union all
				
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 ='$vendor' and MO ='$mo' group by 料品編號,作業站						
										union all
							
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 ='$vendor' and MO ='$mo' group by 料品編號,作業站

										";		
					
					}else if($week<>"ALL" & $mo<>"ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 ='$vendor' and 同欣週 ='$week' and MO ='$mo' group by D料品編號,作業站
										union all
								
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 ='$vendor' and 同欣週 ='$week' and MO ='$mo' group by 料品編號,作業站
										union all
				
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 ='$vendor' and 同欣週 ='$week' and MO ='$mo' group by 料品編號,作業站						
										union all
							
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 ='$vendor' and 同欣週 ='$week' and MO ='$mo' group by 料品編號,作業站

										";		
							
					}
																		


								$conn->conn('wip');
								$conn->result($sql);
								
					
									
									$客戶群組=[];
									$device=[];
									$客戶版本=[];
									$作業站=[];
									$output=[];
									$GrossDIE=[];
									$FrameDIE=[];
									$TT_GrossDIE=[];
									$TT_FrameDIE=[];
									$同欣週=[];
									
									while ($conn->get_row()) {

										$客戶群組[] = $conn->row['客戶群組'];
										$device[] = $conn->row['DEVICE'];
										$客戶版本[] = $conn->row['客戶版本'];
										$作業站[] = $conn->row['作業站'];                                    						
										$output[] = $conn->row['output'];              			
										$GrossDIE[] = $conn->row['GrossDIE'];
										$FrameDIE[] = $conn->row['FrameDIE'];
										$TT_GrossDIE[] = $conn->row['TT_GrossDIE'];
										$TT_FrameDIE[] = $conn->row['TT_FrameDIE'];						
										$同欣週[] = $conn->row['同欣週'];										
										
									}				
									
									
							
								
				/* HOLD段 */
				
					if($week=="ALL" & $mo=="ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 ='$vendor' group by DEVICE,客戶版本,作業站
										union all																				
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 ='$vendor' group by DEVICE,客戶版本,作業站
										union all										
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 ='$vendor' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 ='$vendor' group by DEVICE,客戶版本,作業站
										";
					
					}else if($week<>"ALL" & $mo=="ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 ='$vendor' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站
										union all																				
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 ='$vendor' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站
										union all										
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 ='$vendor' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 ='$vendor' and 同欣週 = '$week' group by DEVICE,客戶版本,作業站
										";
					
					}else if($week=="ALL" & $mo<>"ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 ='$vendor' and MO = '$mo' group by DEVICE,客戶版本,作業站
										union all																				
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 ='$vendor' and MO = '$mo' group by DEVICE,客戶版本,作業站
										union all										
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 ='$vendor' and MO = '$mo' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 ='$vendor' and MO = '$mo' group by DEVICE,客戶版本,作業站
										";	
					
					}else if($week<>"ALL" & $mo<>"ALL"){
						
								$sql = "SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 ='$vendor' and 同欣週 = '$week' and MO = '$mo' group by DEVICE,客戶版本,作業站
										union all																				
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 ='$vendor' and 同欣週 = '$week' and MO = '$mo' group by DEVICE,客戶版本,作業站
										union all										
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 ='$vendor' and 同欣週 = '$week' and MO = '$mo' group by DEVICE,客戶版本,作業站						
										union all
										SELECT 同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 ='$vendor' and 同欣週 = '$week' and MO = '$mo' group by DEVICE,客戶版本,作業站
										";	
							
					}
										

								$conn->result($sql);
								
																
									$客戶群組_hold=[];
									$device_hold=[];
									$客戶版本_hold=[];
									$作業站_hold=[];
									$output_hold=[];
									$GrossDIE_hold=[];
									$FrameDIE_hold=[];
									$TT_GrossDIE_hold=[];
									$TT_FrameDIE_hold=[];

									while ($conn->get_row()) {

										$客戶群組_hold[] = $conn->row['客戶群組'];
										$device_hold[] = $conn->row['DEVICE'];
										$客戶版本_hold[] = $conn->row['客戶版本'];
										$作業站_hold[] = $conn->row['作業站'];                                    						
										$output_hold[] = $conn->row['output'];              			
										$GrossDIE_hold[] = $conn->row['GrossDIE'];
										$FrameDIE_hold[] = $conn->row['FrameDIE'];
										$TT_GrossDIE_hold[] = $conn->row['TT_GrossDIE'];
										$TT_FrameDIE_hold[] = $conn->row['TT_FrameDIE'];						
										
									}				
					


								
								if(isset($客戶群組) && isset($客戶群組_hold)){
						
													
									$wip_ans=array(
													"客戶群組"=>$客戶群組,
													"device"=>$device,
													"客戶版本"=>$客戶版本,
													"作業站"=>$作業站,
													"output"=>$output,
													"GrossDIE"=>$GrossDIE,
													"FrameDIE"=>$FrameDIE,
													"TT_GrossDIE"=>$TT_GrossDIE,
													"TT_FrameDIE"=>$TT_FrameDIE,
													
													"客戶群組_hold"=>$客戶群組_hold,
													"device_hold"=>$device_hold,
													"客戶版本_hold"=>$客戶版本_hold,
													"作業站_hold"=>$作業站_hold,
													"output_hold"=>$output_hold,
													"GrossDIE_hold"=>$GrossDIE_hold,
													"FrameDIE_hold"=>$FrameDIE_hold,
													"TT_GrossDIE_hold"=>$TT_GrossDIE_hold,
													"TT_FrameDIE_hold"=>$TT_FrameDIE_hold,
													
													"客戶群組_hold"=>$客戶群組_hold,
													"device_hold"=>$device_hold,
													"客戶版本_hold"=>$客戶版本_hold,
													"作業站_hold"=>$作業站_hold,
													"output_hold"=>$output_hold

													);					
									return $wip_ans;
									
								}else if(isset($客戶群組) && isset($客戶群組_hold)==false){
									
													
									$wip_ans=array(
													"客戶群組"=>$客戶群組,
													"device"=>$device,
													"客戶版本"=>$客戶版本,
													"作業站"=>$作業站,
													"output"=>$output,
													"GrossDIE"=>$GrossDIE,
													"FrameDIE"=>$FrameDIE,
													"TT_GrossDIE"=>$TT_GrossDIE,
													"TT_FrameDIE"=>$TT_FrameDIE,
													
													"客戶群組_hold"=>[],
													"device_hold"=>[],
													"客戶版本_hold"=>[],
													"作業站_hold"=>[],
													"output_hold"=>[],
													"GrossDIE_hold"=>[],
													"FrameDIE_hold"=>[],
													"TT_GrossDIE_hold"=>[],
													"TT_FrameDIE_hold"=>[]
													);					
									return $wip_ans;
								}


			break;
		}
	}

//////取現在當下的wip_process

//////wip_check_now_process

function wip_check_now_process($process){

	$conn = new create_conn;
	$conn->conn('wip');
	if($process=="S059"){
		$table ='ds_now';
	}else if($process=="S060"){
		$table = 'ds_now';
	}else if($process=="S081"){
		$table = 'rw_now';		
	}else if($process=="S093"){
		$table = 'rw_now';
	}else if($process=="S020"){
		$table = 'gr_now';				
	
	}else{
		$table = $process."_now";
	}

	if($process=="S059"){
		$sql = "SELECT sum(`OUTPUT`) as output from $table WHERE `作業站`='S059'";
	}else if($process=="S060"){
		$sql = "SELECT sum(`OUTPUT`) as output from $table WHERE `作業站`='S060'";
	}else if($process=="S081"){
		$sql = "SELECT sum(`OUTPUT`) as output from $table WHERE `作業站`='S081'";		
	}else if($process=="S093"){
		$sql = "SELECT sum(`OUTPUT`) as output from $table WHERE `作業站`='S093' or `作業站`='S090'";				
	}else if($process=="S020"){
		$sql = "SELECT sum(`OUTPUT`) as output from $table WHERE `作業站`='S020'";			
	}else{
		$sql = "SELECT sum(`OUTPUT`) as output from $table WHERE 1";
	}


	
	$conn->result($sql);

	$output=[];

	while($conn->get_row()){

		$output[] = $conn->row['output'];
	
	}
	
		$output = array_sum($output);
	
	return $output;
}


//////取歷史資料7:00~8:00的wip_check_process 

function wip_check_process($process){

	$conn = new create_conn;
	$conn->conn('wip');
	if($process=="S059"){
		$table ='ds_history';
	}else if($process=="S060"){
		$table = 'ds_history';
	}else if($process=="S081"){
		$table = 'rw_history';		
	}else if($process=="S093" || $process=="S097"){
		$table = 'rw_history';
	}else if($process=="S020"){
		$table = 'gr_history';				
	
	}else{
		$table = $process."_history";
	}

	if($process=="S059"){
		$sql = "SELECT date,sum(`OUTPUT`) as output from $table WHERE `作業站`='S059' group by date";
	}else if($process=="S060"){
		$sql = "SELECT date,sum(`OUTPUT`) as output from $table WHERE `作業站`='S060' group by date";
	}else if($process=="S081"){
		$sql = "SELECT date,sum(`OUTPUT`) as output from $table WHERE `作業站`='S081' group by date";		
	}else if($process=="S093"){
		$sql = "SELECT date,sum(`OUTPUT`) as output from $table WHERE `作業站`='S093' or `作業站`='S090' group by date";				
	}else if($process=="S020"){
		$sql = "SELECT date,sum(`OUTPUT`) as output from $table WHERE `作業站`='S020' group by date";			
	}else if($process=="S097"){
		$sql = "SELECT date,sum(`OUTPUT`) as output from $table WHERE `作業站`='S097' group by date";					
	}else{
		$sql = "SELECT date,sum(`OUTPUT`) as output from $table WHERE 1 group by date";
	}

	$conn->result($sql);

	$output=[];
	$date=[];
	while($conn->get_row()){

		$output[] = $conn->row['output'];
		$date[] = $conn->row['date'];
	}
	
	$ans = array(
		"output"=>$output,
		"date_wip"=>$date
	);
	
	return $ans;
}




//////wip_check_device

function wip_check_device($vendor,$device_str){

	$conn = new create_conn;

	switch($vendor){
		
		case 'CP' :
		
			//NOW
			

			$sql = "SELECT MO,同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now` WHERE `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
					";
			
			//	UNION ALL
			//					SELECT DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_pass` WHERE device<>'' group by DEVICE,客戶版本,作業站								
			$conn->conn('wip');
			$conn->result($sql);
			

			
				$客戶群組=[];
				$device=[];
				$客戶版本=[];
				$作業站=[];
				$同欣週=[];
				$MO=[];

				$output=[];
				
				while ($conn->get_row()) {

					$客戶群組[] = $conn->row['客戶群組'];							
					$device[] = $conn->row['DEVICE'];
					$客戶版本[] = $conn->row['客戶版本'];
					$作業站[] = $conn->row['作業站'];                                    						
					$output[] = $conn->row['output'];     
					$同欣週[] = $conn->row['同欣週'];
					$MO[] = $conn->row['MO'];
			
			
				}				
				

			
		//HOLD
		

			$sql = "SELECT MO,同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_hold` WHERE `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO";
												
			$conn->result($sql);
		
			
				$客戶群組_hold=[];
				$device_hold=[];
				$客戶版本_hold=[];
				$作業站_hold=[];
				$output_hold=[];
				$MO_hold=[];
				
				while ($conn->get_row()) {

					$客戶群組_hold[] = $conn->row['客戶群組'];
					$device_hold[] = $conn->row['DEVICE'];
					$客戶版本_hold[] = $conn->row['客戶版本'];
					$作業站_hold[] = $conn->row['作業站'];                                    						
					$output_hold[] = $conn->row['output'];     
					$MO_hold[] = $conn->row['MO'];
			
				}				
				
				
					
			
			if(isset($客戶群組) && isset($客戶群組_hold)){
			
				$wip_ans=array(
								"客戶群組"=>$客戶群組,
								"device"=>$device,
								"客戶版本"=>$客戶版本,
								"作業站"=>$作業站,
								"output"=>$output,
								"MO"=>$MO,
								"客戶群組_hold"=>$客戶群組_hold,
								"device_hold"=>$device_hold,
								"客戶版本_hold"=>$客戶版本_hold,
								"作業站_hold"=>$作業站_hold,
								"output_hold"=>$output_hold,
								"MO_hold"=>$MO_hold
								
								);
							   
				return $wip_ans;
			
			}else if(isset($客戶群組) && isset($客戶群組_hold)==false){
							

				
				$wip_ans=array(
								"客戶群組"=>$客戶群組,
								"device"=>$device,
								"客戶版本"=>$客戶版本,
								"作業站"=>$作業站,
								"output"=>$output,
								"MO"=>$MO,
								"客戶群組_hold"=>[],
								"device_hold"=>[],
								"客戶版本_hold"=>[],
								"作業站_hold"=>[],
								"output_hold"=>[],									
								"MO_hold"=>[]
								
								);						
				
				return $wip_ans;
				
			}					

		break;
		
///////////
		case 'CP_by_lot' :
		
//							UNION ALL
//							SELECT DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_pass_by_lot` WHERE 1 group by DEVICE,客戶版本,作業站";/				

			$conn = new create_conn;
				
			$sql = "SELECT MO,同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_by_lot` WHERE `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO";
									
			$conn->conn('wip');
			$conn->result($sql);
			
		
			
				$客戶群組=[];
				$device=[];
				$客戶版本=[];
				$作業站=[];
				$output=[];
				$同欣週=[];
				$MO=[];
				
				while ($conn->get_row()) {

					$客戶群組[] = $conn->row['客戶群組'];
					$device[] = $conn->row['DEVICE'];
					$客戶版本[] = $conn->row['客戶版本'];
					$作業站[] = $conn->row['作業站'];                                    						
					$output[] = $conn->row['output'];       
					$同欣週[] = $conn->row['同欣週'];
					$MO[] = $conn->row['MO'];
				}				
				
				
	
			
			/* HOLD */	
			
			$sql = "SELECT MO,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now_hold_by_lot` WHERE `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO";
												
			$conn->result($sql);			
			

			
				$客戶群組_hold=[];
				$device_hold=[];
				$客戶版本_hold=[];
				$作業站_hold=[];

				$output_hold=[];
				$MO_hold=[];
				
				while ($conn->get_row()) {

					$客戶群組_hold[] = $conn->row['客戶群組'];
					$device_hold[] = $conn->row['DEVICE'];
					$客戶版本_hold[] = $conn->row['客戶版本'];
					$作業站_hold[] = $conn->row['作業站'];                                    						
					$output_hold[] = $conn->row['output'];              			
					$MO_hold[] = $conn->row['MO'];
				}				
				
				

			
			$wip_ans=array(
							"客戶群組"=>$客戶群組,
							"device"=>$device,
							"客戶版本"=>$客戶版本,
							"作業站"=>$作業站,
							"output"=>$output,
							"MO"=>$MO,
							"客戶群組_hold"=>$客戶群組_hold,
							"device_hold"=>$device_hold,
							"客戶版本_hold"=>$客戶版本_hold,
							"作業站_hold"=>$作業站_hold,
							"output_hold"=>$output_hold,
							"MO_hold"=>$MO_hold
							
							);
						   
			return $wip_ans;

		break;


///////////			


		case '小客戶' :

						$conn = new create_conn;

						$sql = "SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								union all
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								union all
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO						
								union all
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								";		


						$conn->conn('wip');
						$conn->result($sql);
						
												
							$客戶群組=[];
							$device=[];
							$客戶版本=[];
							$作業站=[];
							$output=[];
							$GrossDIE=[];
							$FrameDIE=[];
							$TT_GrossDIE=[];
							$TT_FrameDIE=[];
							$同欣週=[];
							$MO=[];

							while ($conn->get_row()) {

								$客戶群組[] = $conn->row['客戶群組'];
								$device[] = $conn->row['DEVICE'];
								$客戶版本[] = $conn->row['客戶版本'];
								$作業站[] = $conn->row['作業站'];                                    						
								$output[] = $conn->row['output'];              			
								$GrossDIE[] = $conn->row['GrossDIE'];
								$FrameDIE[] = $conn->row['FrameDIE'];
								$TT_GrossDIE[] = $conn->row['TT_GrossDIE'];
								$TT_FrameDIE[] = $conn->row['TT_FrameDIE'];						
								$同欣週[] = $conn->row['同欣週'];										
								$MO[] = $conn->row['MO'];
								
							}				
							
				
						

				
						$sql = "SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND 客戶群組<>'' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								union all
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								union all										
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								union all
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 <>'A001' AND 客戶群組 <>'A014' AND 客戶群組 <>'A069' AND  客戶群組<>'' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								";		
			


						$conn->result($sql);			
						
						
							
							$客戶群組_hold=[];
							$device_hold=[];
							$客戶版本_hold=[];
							$作業站_hold=[];
							$output_hold=[];
							$GrossDIE_hold=[];
							$FrameDIE_hold=[];
							$TT_GrossDIE_hold=[];
							$TT_FrameDIE_hold=[];
							$MO_hold=[];

							while ($conn->get_row()) {

								$客戶群組_hold[] = $conn->row['客戶群組'];
								$device_hold[] = $conn->row['DEVICE'];
								$客戶版本_hold[] = $conn->row['客戶版本'];
								$作業站_hold[] = $conn->row['作業站'];                                    						
								$output_hold[] = $conn->row['output'];              			
								$GrossDIE_hold[] = $conn->row['GrossDIE'];
								$FrameDIE_hold[] = $conn->row['FrameDIE'];
								$TT_GrossDIE_hold[] = $conn->row['TT_GrossDIE'];
								$TT_FrameDIE_hold[] = $conn->row['TT_FrameDIE'];						
								$MO_hold[] = $conn->row['MO'];
								
							}				
							
							
													
		/* HOLD段 */
						
						if(isset($客戶群組) && isset($客戶群組_hold)){
						
							$wip_ans=array(
											"客戶群組"=>$客戶群組,
											"device"=>$device,
											"客戶版本"=>$客戶版本,
											"作業站"=>$作業站,
											"output"=>$output,
											"GrossDIE"=>$GrossDIE,
											"FrameDIE"=>$FrameDIE,
											"TT_GrossDIE"=>$TT_GrossDIE,
											"TT_FrameDIE"=>$TT_FrameDIE,
											"MO"=>$MO,
											"客戶群組_hold"=>$客戶群組_hold,
											"device_hold"=>$device_hold,
											"客戶版本_hold"=>$客戶版本_hold,
											"作業站_hold"=>$作業站_hold,
											"output_hold"=>$output_hold,
											"GrossDIE_hold"=>$GrossDIE_hold,
											"FrameDIE_hold"=>$FrameDIE_hold,
											"TT_GrossDIE_hold"=>$TT_GrossDIE_hold,
											"TT_FrameDIE_hold"=>$TT_FrameDIE_hold,
											"MO_hold"=>$MO_hold
											);									

							return $wip_ans;
							
						}else if(isset($客戶群組) && isset($客戶群組_hold)==false){		
						
							$wip_ans=array(
											"客戶群組"=>$客戶群組,
											"device"=>$device,
											"客戶版本"=>$客戶版本,
											"作業站"=>$作業站,
											"output"=>$output,
											"GrossDIE"=>$GrossDIE,
											"FrameDIE"=>$FrameDIE,
											"MO"=>$MO,
											"TT_GrossDIE"=>[],
											"TT_FrameDIE"=>[],
											"客戶群組_hold"=>[],
											"device_hold"=>[],
											"客戶版本_hold"=>[],
											"作業站_hold"=>[],
											"output_hold"=>[],
											"GrossDIE_hold"=>[],
											"FrameDIE_hold"=>[],
											"TT_GrossDIE_hold"=>[],
											"TT_FrameDIE_hold"=>[],
											"MO_hold"=>[]
											);									

							return $wip_ans;

						}									

	break;

	
///////////	

		default:
		
						$conn = new create_conn;						
				
						$sql = "SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now` WHERE 客戶群組 ='$vendor' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								union all
						
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now` WHERE 客戶群組 ='$vendor' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								union all
		
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now` WHERE 客戶群組 ='$vendor' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO						
								union all
					
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now` WHERE 客戶群組 ='$vendor' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO

								";
									



						$conn->conn('wip');
						$conn->result($sql);
						
						
							
							$客戶群組=[];
							$device=[];
							$客戶版本=[];
							$作業站=[];
							$output=[];
							$GrossDIE=[];
							$FrameDIE=[];
							$TT_GrossDIE=[];
							$TT_FrameDIE=[];
							$同欣週=[];
							$MO=[];
							
							while ($conn->get_row()) {

								$客戶群組[] = $conn->row['客戶群組'];
								$device[] = $conn->row['DEVICE'];
								$客戶版本[] = $conn->row['客戶版本'];
								$作業站[] = $conn->row['作業站'];                                    						
								$output[] = $conn->row['output'];              			
								$GrossDIE[] = $conn->row['GrossDIE'];
								$FrameDIE[] = $conn->row['FrameDIE'];
								$TT_GrossDIE[] = $conn->row['TT_GrossDIE'];
								$TT_FrameDIE[] = $conn->row['TT_FrameDIE'];						
								$同欣週[] = $conn->row['同欣週'];										
								$MO[] = $conn->row['MO'];
								
							}				
							
							
						
						
		/* HOLD段 */
		

				
						$sql = "SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `gr_now_hold` WHERE 客戶群組 ='$vendor' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								union all																				
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `ds_now_hold` WHERE 客戶群組 ='$vendor' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								union all										
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `grds_now_hold` WHERE 客戶群組 ='$vendor' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO						
								union all
								SELECT MO,同欣週,客戶群組,DEVICE,客戶版本,作業站,sum(`output`) as output,GrossDIE,FrameDIE,sum(`TT_GrossDIE`) as TT_GrossDIE, sum(`TT_FrameDIE`) as TT_FrameDIE FROM `rw_now_hold` WHERE 客戶群組 ='$vendor' AND `DEVICE` = '$device_str' group by DEVICE,客戶版本,作業站,MO
								";

						

						$conn->result($sql);
						
												
							$客戶群組_hold=[];
							$device_hold=[];
							$客戶版本_hold=[];
							$作業站_hold=[];
							$output_hold=[];
							$GrossDIE_hold=[];
							$FrameDIE_hold=[];
							$TT_GrossDIE_hold=[];
							$TT_FrameDIE_hold=[];
							$MO_hold=[];

							while ($conn->get_row()) {

								$客戶群組_hold[] = $conn->row['客戶群組'];
								$device_hold[] = $conn->row['DEVICE'];
								$客戶版本_hold[] = $conn->row['客戶版本'];
								$作業站_hold[] = $conn->row['作業站'];                                    						
								$output_hold[] = $conn->row['output'];              			
								$GrossDIE_hold[] = $conn->row['GrossDIE'];
								$FrameDIE_hold[] = $conn->row['FrameDIE'];
								$TT_GrossDIE_hold[] = $conn->row['TT_GrossDIE'];
								$TT_FrameDIE_hold[] = $conn->row['TT_FrameDIE'];	
								$MO_hold[] = $conn->row['MO'];					
								
							}				
							
							
						
						
						
						if(isset($客戶群組) && isset($客戶群組_hold)){
				
											
							$wip_ans=array(
											"客戶群組"=>$客戶群組,
											"device"=>$device,
											"客戶版本"=>$客戶版本,
											"作業站"=>$作業站,
											"output"=>$output,
											"GrossDIE"=>$GrossDIE,
											"FrameDIE"=>$FrameDIE,
											"TT_GrossDIE"=>$TT_GrossDIE,
											"TT_FrameDIE"=>$TT_FrameDIE,
											"MO"=>$MO,
											
											"客戶群組_hold"=>$客戶群組_hold,
											"device_hold"=>$device_hold,
											"客戶版本_hold"=>$客戶版本_hold,
											"作業站_hold"=>$作業站_hold,
											"output_hold"=>$output_hold,
											"GrossDIE_hold"=>$GrossDIE_hold,
											"FrameDIE_hold"=>$FrameDIE_hold,
											"TT_GrossDIE_hold"=>$TT_GrossDIE_hold,
											"TT_FrameDIE_hold"=>$TT_FrameDIE_hold,
											"MO_hold"=>$MO_hold														
											);					
							return $wip_ans;
							
						}else if(isset($客戶群組) && isset($客戶群組_hold)==false){
							
											
							$wip_ans=array(
											"客戶群組"=>$客戶群組,
											"device"=>$device,
											"客戶版本"=>$客戶版本,
											"作業站"=>$作業站,
											"output"=>$output,
											"GrossDIE"=>$GrossDIE,
											"FrameDIE"=>$FrameDIE,
											"TT_GrossDIE"=>$TT_GrossDIE,
											"TT_FrameDIE"=>$TT_FrameDIE,
											"MO"=>$MO,
											
											"客戶群組_hold"=>[],
											"device_hold"=>[],
											"客戶版本_hold"=>[],
											"作業站_hold"=>[],
											"output_hold"=>[],
											"GrossDIE_hold"=>[],
											"FrameDIE_hold"=>[],
											"TT_GrossDIE_hold"=>[],
											"TT_FrameDIE_hold"=>[],
											"MO_hold"=>[]
											);					
							return $wip_ans;
						}


	break;
}
}
        
}