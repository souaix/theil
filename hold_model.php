<?php
//wip for hold_now.php
//此類別適用於MFG，不分客戶，只分時間站別

class hold_now{

	function __construct() {
		require_once(ROOT.'connect.php');   
	}


	//wip UPDATE time
	function wip_new_update_log(){

		$conn = new create_conn;
		$conn->conn('wip');
		$sql = "SELECT `log` FROM `wip_new_update_log` where 1 order by sn DESC limit 1";
		$conn->result($sql);
		while($conn->get_row()) {
			$log = $conn->row['log'];
			return $log;
		}
	}


//從date、站別 or mo 找HOLD清單 for DASHBOARD
function hold_list_dashboard($date1,$date2,$begin,$end){
		
	$conn = new create_conn;
	$conn->conn('wip');		
	$begin = substr($begin,-3);
	$end = substr($end,-3);
			
	//找站別區間、日期區間有HOLD的MO output

			$sql = "SELECT 下線類別,date,MO,sum(`OUTPUT`) as `output` FROM `gr_history` WHERE (RIGHT(`作業站`,3) between $begin and $end) and (date between '$date1' and '$date2') and (`STATUS` ='HOLD' or `STATUS` ='MRB') group by MO,date,下線類別
					union all
					SELECT 下線類別,date,MO,sum(`OUTPUT`) as `output` FROM `ds_history` WHERE (RIGHT(`作業站`,3) between $begin and $end) and (date between '$date1' and '$date2') and (`STATUS` ='HOLD' or `STATUS` ='MRB') group by MO,date,下線類別
					union all
					SELECT 下線類別,date,MO,sum(`OUTPUT`) as `output` FROM `grds_history` WHERE (RIGHT(`作業站`,3) between $begin and $end) and (date between '$date1' and '$date2') and (`STATUS` ='HOLD' or `STATUS` ='MRB') group by MO,date,下線類別
					union all							
					SELECT 下線類別,date,MO,sum(`OUTPUT`) as `output` FROM `rw_history` WHERE (RIGHT(`作業站`,3) between $begin and $end) and (date between '$date1' and '$date2') and (`STATUS` ='HOLD' or `STATUS` ='MRB') group by MO,date,下線類別
					";							


				$conn->result($sql);			
				$date=[];
				$MO=[];
				$output=[];
				$下線類別=[];                    

				while($conn->get_row()){

					if(strpos($conn->row['date'],"07:00")!=false){
						$date[] = substr($conn->row['date'],0,-3);
						$下線類別[] = $conn->row['下線類別'];
						$MO[] = $conn->row['MO'];
						$output[] = $conn->row['output'];
					}

				}
				
				//用MO與date去回找被HOLD的LOT總數與與DEVICE
				$tt_output=[];

				for($i=0;$i<count($MO);$i++){

					$mo=$MO[$i];
					$dd=$date[$i].":00";

				
					$tt_output_temp=[];

					$sql = "SELECT sum(`OUTPUT`) as `tt_output`,`DEVICE` from `gr_history` where MO ='$mo' and date='$dd'
							union all
							SELECT sum(`OUTPUT`) as `tt_output`,`DEVICE` from `grds_history` where MO ='$mo' and date='$dd'
							union all
							SELECT sum(`OUTPUT`) as `tt_output`,`DEVICE` from `ds_history` where MO ='$mo' and date='$dd'
							union all
							SELECT sum(`OUTPUT`) as `tt_output`,`DEVICE` from `rw_history` where MO ='$mo' and date='$dd'";

					$conn->result($sql);

					while($conn->get_row()){

						$tt_output_temp[]=$conn->row['tt_output'];



					}

					$tt_output[] = array_sum($tt_output_temp);


				}

				$ans = array(

					"date"=>$date,
					"下線類別"=>$下線類別,
					"output"=>$output,
					"tt_output"=>$tt_output,
					"mo"=>$MO

				);

				return $ans;

						
}

//從找HOLD CODE 清單 for DASHBOARD (找now only)
function hold_code_dashboard($begin,$end){

	$conn = new create_conn;
	$conn->conn('wip');		
	$begin = substr($begin,-3);
	$end = substr($end,-3);
			
	//找站別區間、日期區間有HOLD的MO output

			$sql = "SELECT `HOLD CODE`,下線類別,sum(`片數`) as `output` FROM `gr_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `HOLD CODE`,下線類別
					union all
					SELECT `HOLD CODE`,下線類別,sum(`片數`) as `output` FROM `ds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `HOLD CODE`,下線類別
					union all
					SELECT `HOLD CODE`,下線類別,sum(`片數`) as `output` FROM `grds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `HOLD CODE`,下線類別
					union all							
					SELECT `HOLD CODE`,下線類別,sum(`片數`) as `output` FROM `rw_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `HOLD CODE`,下線類別
					";		
		
			$conn->result($sql);

			$下線類別=[];
			$hold_code=[];
			$output=[];

			while($conn->get_row()){

				$下線類別[]=$conn->row['下線類別'];
				$hold_code[]=$conn->row['HOLD CODE'];
				$output[] = $conn->row['output'];

			}


			$ans=array(
				"下線類別"=>$下線類別,
				"hold_code"=>$hold_code,
				"output"=>$output
			);

			return $ans;

}

//從找HOLD DEVICE 清單 for DASHBOARD (找now only)
function hold_device_dashboard($begin,$end){

	$conn = new create_conn;
	$conn->conn('wip');		
	$begin = substr($begin,-3);
	$end = substr($end,-3);
			
	//找站別區間、日期區間有HOLD的MO output

			$sql = "SELECT DEVICE,下線類別,sum(`片數`) as `output` FROM `gr_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by DEVICE,下線類別
					union all
					SELECT DEVICE,下線類別,sum(`片數`) as `output` FROM `ds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by DEVICE,下線類別
					union all
					SELECT DEVICE,下線類別,sum(`片數`) as `output` FROM `grds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by DEVICE,下線類別
					union all							
					SELECT DEVICE,下線類別,sum(`片數`) as `output` FROM `rw_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by DEVICE,下線類別
					";		
			
			$conn->result($sql);

			$下線類別=[];
			$DEVICE=[];
			$output=[];

			while($conn->get_row()){

				$下線類別[]=$conn->row['下線類別'];
				$DEVICE[]=$conn->row['DEVICE'];
				$output[] = $conn->row['output'];

			}


			$ans=array(
				"下線類別"=>$下線類別,
				"device"=>$DEVICE,
				"output"=>$output
			);

			return $ans;

}

	//從DEVICE、站別 or mo 找HOLD清單
	function hold_list($device,$station,$mo,$begin,$end){
		
		$conn = new create_conn;
		$conn->conn('wip');		
		$begin = substr($begin,-3);
		$end = substr($end,-3);

		//mo=0=>找實際被hold數（紅字）

		if($device<>'0' && $station <>'0' && $mo=='0'){

					$sql = "SELECT * FROM `cp_hold` WHERE DEVICE='$device' AND 作業站='$station'  
							union all
							SELECT * FROM `gr_hold` WHERE DEVICE='$device' AND 作業站='$station'
							union all
							SELECT * FROM `ds_hold` WHERE DEVICE='$device' AND 作業站='$station'
							union all
							SELECT * FROM `grds_hold` WHERE DEVICE='$device' AND 作業站='$station'
							union all							
							SELECT * FROM `rw_hold` WHERE DEVICE='$device' AND 作業站='$station'
							";

		//mo<>0=>找被hold影響的lot(橘、藍)
		}else if($device<>'0' && $station <>'0' && $mo<>'0'){
			
					$sql = "SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `cp_hold` WHERE DEVICE='$device' AND 作業站='$station' group by SUBLOT   
							union all
							SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `gr_hold` WHERE DEVICE='$device' AND 作業站='$station' group by SUBLOT  
							union all
							SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `ds_hold` WHERE DEVICE='$device' AND 作業站='$station' group by SUBLOT  
							union all
							SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `grds_hold` WHERE DEVICE='$device' AND 作業站='$station' group by SUBLOT  							
							union all							
							SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `rw_hold` WHERE DEVICE='$device' AND 作業站='$station' group by SUBLOT 
							";			

		//找行total的紅字
		}else if($device<>'0' && $station=='0' && $mo=='0'){

					$sql = "SELECT * FROM `cp_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end)
							union all
							SELECT * FROM `gr_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end)
							union all
							SELECT * FROM `ds_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end)
							union all
							SELECT * FROM `grds_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end)
							union all							
							SELECT * FROM `rw_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end)
							";			

		//找行total的被hold影響的lot(橘、藍)
		}else if($device<>'0' && $station=='0' && $mo<>'0'){

					$sql = "SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `cp_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT   
							union all
							SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `gr_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT  
							union all
							SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `ds_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT  
							union all
							SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `grds_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT  							
							union all							
							SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `rw_hold` WHERE DEVICE='$device' and (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT 
							";					

		//找列total的紅字
		}else if($device=='0' && $station<>'0' && $mo=='0'){

					$sql = "SELECT * FROM `cp_hold` WHERE 作業站='$station' 
							union all
							SELECT * FROM `gr_hold` WHERE 作業站='$station' 
							union all
							SELECT * FROM `ds_hold` WHERE 作業站='$station' 
							union all
							SELECT * FROM `grds_hold` WHERE 作業站='$station' 							
							union all							
							SELECT * FROM `rw_hold` WHERE 作業站='$station'
							";			

		//找列total的被hold影響的lot(橘、藍)
		}else if($device=='0' && $station<>'0' && $mo<>'0'){

				$sql = "SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `cp_hold` WHERE 作業站='$station' group by SUBLOT   
						union all
						SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `gr_hold` WHERE 作業站='$station' group by SUBLOT  
						union all
						SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `ds_hold` WHERE 作業站='$station' group by SUBLOT  
						union all
						SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `grds_hold` WHERE 作業站='$station' group by SUBLOT  							
						union all							
						SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `rw_hold` WHERE 作業站='$station' group by SUBLOT 
						";					


		//找列總total的紅字
		}else if($device=='0' && $station=='0' && $mo=='0'){

				$sql = "SELECT * FROM `cp_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end)
						union all
						SELECT * FROM `gr_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end)
						union all
						SELECT * FROM `ds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end)
						union all
						SELECT * FROM `grds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end)							
						union all							
						SELECT * FROM `rw_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end)
						";							

		//找列total的被hold影響的lot(橘、藍)
		}else if($device=='0' && $station=='0' && $mo<>'0'){

				$sql = "SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `cp_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT   
						union all
						SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `gr_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT  
						union all
						SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `ds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT  
						union all
						SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `grds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT  							
						union all							
						SELECT left(SUBLOT,12) AS `SUBLOT`,DEVICE,客戶版本,同欣週,片數,已過帳數量,下線類別,`下線時間(hr)`,`進段時間(hr)`,`進站時間(hr)`,作業站,STATUS,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`,`異常原因`,主因1,主因2,SublotCount,料品編號,優先等級,指定BIN,`Gross Die`,`Frame Die` FROM `rw_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by SUBLOT 
						";					


		}

		
					$conn->result($sql);			
					$device=[];
					$客戶版本=[];
					$下線類別=[];
					$sublot=[];
					$同欣週=[];
					$片數=[];
					$已過帳數量=[];
					$下線時間=[];
					$進段時間=[];
					$進站時間=[];
					$作業站=[];
					$status=[];
					$status_time=[];
					$hold_code=[];
					$hold_code說明=[];
					$異常原因=[];
					$主因1=[];
					$主因2=[];
					$sublotcount=[];
					$料品編號=[];
					$優先等級=[];
					$指定bin=[];
					$gross_die=[];
					$frame_die=[];

					while($conn->get_row()){

						$device[] = $conn->row['DEVICE'];
						$客戶版本[] = $conn->row['客戶版本'];
						$下線類別[] = $conn->row['下線類別'];
						$sublot[] = $conn->row['SUBLOT'];
						$同欣週[] = $conn->row['同欣週'];
						$片數[] = $conn->row['片數'];
						$已過帳數量[] = $conn->row['已過帳數量'];
						$下線時間[] = $conn->row['下線時間(hr)'];
						$進段時間[] = $conn->row['進段時間(hr)'];
						$進站時間[] = $conn->row['進站時間(hr)'];
						$作業站[] = $conn->row['作業站'];
						$status[] = $conn->row['STATUS'];
						$status_time[] = $conn->row['STATUS_TIME'];
						$hold_code[] = $conn->row['HOLD CODE'];
						$hold_code說明[] = $conn->row['HOLD CODE說明'];
						$異常原因[] = $conn->row['異常原因'];
						$主因1[] = $conn->row['主因1'];
						$主因2[] = $conn->row['主因2'];
						$sublotcount[] = $conn->row['SublotCount'];
						$料品編號[] = $conn->row['料品編號'];
						$優先等級[] = $conn->row['優先等級'];
						$指定bin[] = $conn->row['指定BIN'];
						$gross_die[] = $conn->row['Gross Die'];
						$frame_die[] = $conn->row['Frame Die'];

					}

					$ans = array(

						"device"=>$device,
						"客戶版本"=>$客戶版本,
						"下線類別"=>$下線類別,
						"sublot"=>$sublot,
						"同欣週"=>$同欣週,
						"片數"=>$片數,
						"已過帳數量"=>$已過帳數量,
						"下線時間(hr)"=>$下線時間,
						"進段時間(hr)"=>$進段時間,
						"進站時間(hr)"=>$進站時間,
						"作業站"=>$作業站,
						"status"=>$status,
						"status_time"=>$status_time,
						"hold_code"=>$hold_code,
						"hold_code說明"=>$hold_code說明,
						"異常原因"=>$異常原因,
						"主因1"=>$主因1,
						"主因2"=>$主因2,
						"sublotcount"=>$sublotcount,
						"料品編號"=>$料品編號,
						"優先等級"=>$優先等級,
						"指定BIN"=>$指定bin,
						"gross_die"=>$gross_die,
						"frame_die"=>$frame_die
					);

					return $ans;

							
	}
			//for 找hold mo的詳細資訊
			function hold_mo_info($mo){
				$conn = new create_conn;
				$conn->conn('wip');
		
					$sql = "SELECT `DEVICE`,`SUBLOT`,`STATUS`,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`, `異常原因`,`主因1`,`主因2`,left(SUBLOT,12) AS `MO`,`DEVICE`,下線類別,作業站,`片數` from `rw_new` where SUBLOT like '%$mo%'
							union all
							SELECT `DEVICE`,`SUBLOT`,`STATUS`,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`, `異常原因`,`主因1`,`主因2`,left(SUBLOT,12) AS `MO`,`DEVICE`,下線類別,作業站,`片數` from `gr_new` where SUBLOT like '%$mo%'
							union all
							SELECT `DEVICE`,`SUBLOT`,`STATUS`,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`, `異常原因`,`主因1`,`主因2`,left(SUBLOT,12) AS `MO`,`DEVICE`,下線類別,作業站,`片數` from `ds_new` where SUBLOT like '%$mo%'
							union all
							SELECT `DEVICE`,`SUBLOT`,`STATUS`,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`, `異常原因`,`主因1`,`主因2`,left(SUBLOT,12) AS `MO`,`DEVICE`,下線類別,作業站,`片數` from `grds_new` where SUBLOT like '%$mo%'
							union all
							SELECT `DEVICE`,`SUBLOT`,`STATUS`,STATUS_TIME,`HOLD CODE`,`HOLD CODE說明`, `異常原因`,`主因1`,`主因2`,left(SUBLOT,12) AS `MO`,`DEVICE`,下線類別,作業站,`片數` from `cp_new` where SUBLOT like '%$mo%'";
					
					$conn->result($sql);
		
					$output=[];
					while($conn->get_row()){
		
						$device = $conn->row['DEVICE'];				
						$作業站 = $conn->row['作業站'];
						$下線類別 = $conn->row['下線類別'];
						$output[] = $conn->row['片數'];
						$status = $conn->row['STATUS'];
							if($status=="HOLD" || $status=="MRB"){

								$status_time = $conn->row['STATUS_TIME'];
								$hold_code = $conn->row['HOLD CODE'];
								$hold_code說明 = $conn->row['HOLD CODE說明'];
								$異常原因 = $conn->row['異常原因'];
								$主因1 = $conn->row['主因1'];
								$主因2 = $conn->row['主因2'];
							}
		
					}			
		
						$output = array_sum($output);
		

					//回查被hold數量		
					$sql = "SELECT sum(`OUTPUT`) as OUTPUT from `rw_now_hold` where MO='$mo'
							union all
							SELECT sum(`OUTPUT`) as OUTPUT from `gr_now_hold` where MO='$mo'
							union all
							SELECT sum(`OUTPUT`) as OUTPUT from `ds_now_hold` where MO='$mo'
							union all
							SELECT sum(`OUTPUT`) as OUTPUT from `grds_now_hold` where MO='$mo'
							union all
							SELECT sum(`OUTPUT`) as OUTPUT from `cp_now_hold` where MO='$mo'";
					
					
					$conn->result($sql);
					$act_hold=[];
					while($conn->get_row()){

						$act_hold[] = $conn->row['OUTPUT'];
						
					}
						$act_hold = array_sum($act_hold);

						$ans = array(
									"device"=>$device,
									"作業站"=>$作業站,
									"output"=>$output,
									"下線類別"=>$下線類別,
									"act_hold"=>$act_hold,
									"status_time"=>$status_time,
									"hold_code"=>$hold_code,
									"hold_code說明"=>$hold_code說明,
									"異常原因"=>$異常原因,
									"主因1"=>$主因1,
									"主因2"=>$主因2
						);
		
						return $ans;
		
			}
	

	//從站別區間找hold(實際hold片數)
	function hold_check($begin,$end,$hold_code_option){

		$conn = new create_conn;
		$conn->conn('wip');

		$begin = substr($begin,-3);
		$end = substr($end,-3);

					
					//從now_hold中找所有hold的筆數
					if($hold_code_option=='ALL'){
						$sql = "SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `cp_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `料品編號`,`作業站`,`MO`
								union all
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `gr_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `料品編號`,`作業站`,`MO`
								union all
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `ds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `料品編號`,`作業站`,`MO`
								union all
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `grds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `料品編號`,`作業站`,`MO`							
								union all							
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `rw_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `料品編號`,`作業站`,`MO`
								";
					}else if($hold_code_option=='排除RW025'){
						$sql = "SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `cp_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` <> 'RW025' group by `料品編號`,`作業站`,`MO`
								union all
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `gr_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` <> 'RW025' group by `料品編號`,`作業站`,`MO`
								union all
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `ds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` <> 'RW025' group by `料品編號`,`作業站`,`MO`
								union all
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `grds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` <> 'RW025' group by `料品編號`,`作業站`,`MO`							
								union all							
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `rw_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` <> 'RW025' group by `料品編號`,`作業站`,`MO`
								";
					}else{
						$sql = "SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `cp_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` = '$hold_code_option' group by `料品編號`,`作業站`,`MO`
								union all
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `gr_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` = '$hold_code_option' group by `料品編號`,`作業站`,`MO`
								union all
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `ds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` = '$hold_code_option' group by `料品編號`,`作業站`,`MO`
								union all
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `grds_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` = '$hold_code_option' group by `料品編號`,`作業站`,`MO`							
								union all							
								SELECT 同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `rw_hold` WHERE (RIGHT(`作業站`,3) between $begin and $end) and `HOLD CODE` = '$hold_code_option' group by `料品編號`,`作業站`,`MO`
								";

					}	

					
						
						$conn->result($sql);			
					
					
						$客戶群組_hold=[];
						$device_hold=[];
						$客戶版本_hold=[];
						$作業站_hold=[];
						$output_hold=[];
						$mo_hold=[];
						
						while ($conn->get_row()){
							$station=$conn->row['作業站'];
							if(strpos($station,"S")!==false){
							//	$客戶群組_hold[] = $conn->row['客戶群組'];
								$device_hold[] = $conn->row['DEVICE'];
								$客戶版本_hold[] = $conn->row['客戶版本'];
								$作業站_hold[] = $conn->row['作業站'];                                    						
								$output_hold[] = $conn->row['output']; 
								$mo_hold[] = $conn->row['MO'];

							}    
					
						}				
					
						$mo_lot=[];
						//從mo_hold裡回查_now的受影響lot數
						for($i=0;$i<count($mo_hold);$i++){

							$mo = $mo_hold[$i];
						
							$mo_lot_temp=[];
							$sql = "SELECT sum(`OUTPUT`) as OUTPUT  from `cp_now` where MO = '$mo'
									union all
									SELECT sum(`OUTPUT`) as OUTPUT from `gr_now` where MO = '$mo'
									union all
									SELECT sum(`OUTPUT`) as OUTPUT from `grds_now` where MO = '$mo'
									union all
									SELECT sum(`OUTPUT`) as OUTPUT from `ds_now` where MO = '$mo'
									union all
									SELECT sum(`OUTPUT`) as OUTPUT from `rw_now` where MO = '$mo'
									";

							$conn->result($sql);
							//該mo lot總數存進mo_lot
	

							$num=$conn->num();


								while ($conn->get_row()){

									if($conn->row['OUTPUT']>=0){
										$mo_lot_temp[] = $conn->row['OUTPUT'];

									}else{
										$mo_lot_temp[]=0;
									}
																									
								}

							
							$mo_lot[] = array_sum($mo_lot_temp);
							

						}


						if(isset($客戶群組_hold)){
					
							$wip_ans=array(

											"客戶群組_hold"=>$客戶群組_hold,
											"device_hold"=>$device_hold,
											"客戶版本_hold"=>$客戶版本_hold,
											"作業站_hold"=>$作業站_hold,
											"output_hold"=>$output_hold,
											"mo_hold"=>$mo_hold,
											"mo_lot"=>$mo_lot
											
											);
							
						}else if(isset($客戶群組_hold)==false){
										
	
							
							$wip_ans=array(
											"客戶群組_hold"=>[],
											"device_hold"=>[],
											"客戶版本_hold"=>[],
											"作業站_hold"=>[],
											"output_hold"=>[],
											"mo_hold"=>[],
											"mo_lot"=>[]
											);																				
						}									
						return $wip_ans;
	}					
	

	//找所有wip

    function wip_check($begin,$end){

			$conn = new create_conn;
			$conn->conn('wip');
			$begin = substr($begin,-3);
			$end = substr($end,-3);
/*
					$sql = "SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `cp_now` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by DEVICE,客戶版本,作業站
							UNION ALL
							SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `gr_now` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by DEVICE,客戶版本,作業站 
							UNION ALL
							SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `rw_now` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by DEVICE,客戶版本,作業站 
							UNION ALL
							SELECT 同欣週,DEVICE,客戶版本,作業站,sum(`output`) as output,客戶群組 FROM `ds_now` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by DEVICE,客戶版本,作業站 order by 客戶群組";
							
*/

					$sql = "SELECT `HOLD CODE`,同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `cp_new` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `作業站`,`MO`
							union all
							SELECT `HOLD CODE`,同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `gr_new` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `作業站`,`MO`
							union all
							SELECT `HOLD CODE`,同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `ds_new` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `作業站`,`MO`
							union all
							SELECT `HOLD CODE`,同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `grds_new` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `作業站`,`MO`							
							union all							
							SELECT `HOLD CODE`,同欣週,left(SUBLOT,12) AS MO,料品編號,DEVICE,客戶版本,作業站,sum(`片數`) as output FROM `rw_new` WHERE (RIGHT(`作業站`,3) between $begin and $end) group by `作業站`,`MO`
							";
					
					$conn->result($sql);
					
						$客戶群組=[];
						$device=[];
						$客戶版本=[];
						$作業站=[];
						$同欣週=[];
						//抓取所有code給select
						$hold_code=[];

						$output=[];
						
						while ($conn->get_row()) {
							$station=$conn->row['作業站'];
							if(strpos($station,"S")!==false){
							//	$客戶群組[] = $conn->row['客戶群組'];							
								$device[] = $conn->row['DEVICE'];
								$客戶版本[] = $conn->row['客戶版本'];
								$作業站[] = $conn->row['作業站'];                                    						
								$output[] = round($conn->row['output']);    
								$同欣週[] = $conn->row['同欣週'];
								if($conn->row['HOLD CODE']<>''){
								$hold_code[] = $conn->row['HOLD CODE'];							
								}
							}
										
						}				

						$hold_code=array_values(array_unique($hold_code));

						$wip_ans=array(
										//"客戶群組"=>$客戶群組,
										"device"=>$device,
										"客戶版本"=>$客戶版本,
										"作業站"=>$作業站,
										"output"=>$output,
										"hold_code"=>$hold_code
										);						
						
						return $wip_ans;

	}
}