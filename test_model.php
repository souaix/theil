<?php
//wip for hold_now.php
//此類別適用於MFG，不分客戶，只分時間站別

class test_now{

	function __construct() {
		require_once(ROOT.'connect.php');   
	}

			function move_review_date(){

				$conn = new create_conn;
				$conn->conn('move');				

				$sql = "SELECT date from `move_4hr_history` where 1 group by `date`";

				$conn->result($sql);

				$date=[];

				while($conn->get_row()){

					$date[] = date("Y-m-d",strtotime($conn->row['date']));
				}

					$date = array_values(array_unique($date));

					$date = array_slice($date,-7);

					return $date;
			}
 
			function move_machine_review($date,$device,$station){

				$conn = new create_conn;
				$conn->conn('move');	

				$date1 = $date." 00:00:00";
				$date2 = $date." 23:59:00";

				if($device=='0' && $station<>'0'){

					$sql = "SELECT 晶圓型號,machine,sum(`過帳片數`) as `過帳片數`, sum(`重工片數`) as `重工片數`, sum(`總完工數量`) as `總完工數量` from `move_4hr_history` where `Station` = '$station' and (date between '$date1' and '$date2' ) group by `machine`";				

				}else{
				
					$sql = "SELECT 晶圓型號,machine,sum(`過帳片數`) as `過帳片數`, sum(`重工片數`) as `重工片數`, sum(`總完工數量`) as `總完工數量` from `move_4hr_history` where `晶圓型號` = '$device' and `Station` = '$station' and (date between '$date1' and '$date2' ) group by `machine`";				

				}

				$conn->result($sql);
				
				$過帳片數=[];
				$重工片數=[];
				$總完工數量=[];
				$machine=[];
				$device=[];

				while($conn->get_row()){

					$過帳片數[] = $conn->row['過帳片數'];
					$重工片數[] = $conn->row['重工片數'];
					$總完工數量[] = $conn->row['總完工數量'];
					$machine[] = $conn->row['machine'];
					$device[] = $conn->row['晶圓型號'];
				}

				$ans = array(


					"過帳片數"=>$過帳片數,
					"總完工數量"=>$總完工數量,
					"重工片數"=>$重工片數,
					"machine"=>$machine,
					"device"=>$device

				);

				return $ans;




			}


			//從date、站別 or mo 找HOLD清單 for DASHBOARD
			function move_check($date){
					
				$conn = new create_conn;
				$conn->conn('move');		

						
				//找站別區間、日期區間有HOLD的MO output

							$date1 = $date." 00:00:00";
							$date2 = $date." 23:59:00";

							$sql = "SELECT 晶圓型號,下線類別,sum(`過帳片數`) as `過帳片數`, sum(`重工片數`) as `重工片數`, sum(`總完工數量`) as `總完工數量`,Station from `move_4hr_history` where (date between '$date1' and '$date2' ) group by `晶圓型號`,`下線類別`,`Station`";
									
							$conn->result($sql);			

							$date=[];
							$device=[];
							$過帳片數=[];
							$重工片數=[];
							$總完工片數=[];
							$下線類別=[];
							$station=[];


							while($conn->get_row()){

							//	$date[] = date("Y-m-d",strtotime($conn->row['date']));
								$device[] = $conn->row['晶圓型號'];
								$過帳片數[] = $conn->row['過帳片數'];
								$重工片數[] = $conn->row['重工片數'];
								$總完工數量[] = $conn->row['總完工數量'];
								$下線類別[] = $conn->row['下線類別'];
								$station[] = $conn->row['Station'];

							}


							$ans = array(

								//"date"=>$date,
								"下線類別"=>$下線類別,
								"過帳片數"=>$過帳片數,
								"總完工數量"=>$總完工數量,
								"重工片數"=>$重工片數,
								"device"=>$device,
								"station"=>$station

							);

							return $ans;
			}
					
}