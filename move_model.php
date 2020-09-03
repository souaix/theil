<?php

class move_model{

	function __construct() {
		require_once(ROOT.'connect.php');   
	}

	//四節move撈出
	function move_4hr($date,$option){
		$conn = new create_conn;
		$conn->conn('move');
			$date = date("Y-m-d",strtotime($date.'-2 day'));
			$time1 = $date." 00:00:00";
			$time2 = $date." 23:59:00";
			if($option=="for_dashboard"){
				$sql = "SELECT date,sum(`總完工數量`) as output,Station,下線類別 from move_4hr_history where (date between '$time1' and '$time2') group by Station,下線類別,date";					
				
				$conn->result($sql);

				$output=[];
				$station=[];
				$下線類別=[];
				$date =[];

				while($conn->get_row()){

					$output[] = $conn->row['output'];
					$station[] = $conn->row['Station'];
					$下線類別[] = $conn->row['下線類別'];

					$date_str = $conn->row['date'];
					$hr = date("H:i:s",strtotime($date_str));

					//為了排序，把date轉回真實日期.
					if($hr>=0 && $hr<=7){

					$date_str = date("Y-m-d H:i:s",strtotime($date_str.'+1 day'));					

					}

					$date[] = $date_str;

					$ans=array(

						"output"=>$output,
						"station"=>$station,
						"下線類別"=>$下線類別,
						"date"=>$date
					);

				}				


			}
			
			return $ans;

		

	}
	//每日target data撈出
	function target_day($date1,$date2){
		$conn = new create_conn;
		$conn->conn('ie_parameter');
			//a的foutput
			$date1 = date('Y-m-d',strtotime($date1.'-10 day'));
			$date2 = date('Y-m-d',strtotime($date2.'-10 day'));
			$sql = "SELECT * from daily_target where (date between '$date1' and '$date2')";
			$conn->result($sql);

			if($conn->num()==0){

				$date1 = date('Y-m-d',strtotime($date1.'-10 day'));
				$date2 = date('Y-m-d',strtotime($date2.'-10 day'));
				$sql = "SELECT * from daily_target where (date between '$date1' and '$date2')";
				$conn->result($sql);				

			}

			$date=[];
			$rw_pcs_target=[];
			$rw_ea_target=[];

			$gdsaw_pcs_target=[];
			$gdsaw_ea_target=[];

			$lg_pcs_target=[];
			$lg_ea_target=[];

			$be_pcs_target=[];
			$be_ea_target=[];	
			
			$fe_pcs_target=[];
			$fe_ea_target=[];				

			while($conn->get_row()){

				$date[]=$conn->row['date'];

				$rw_pcs_target[]=$conn->row['rw_pcs_target'];
				$rw_ea_target[]=round($conn->row['rw_ea_target'],2);

				$gdsaw_pcs_target[]=$conn->row['gdsaw_pcs_target'];
				$gdsaw_ea_target[]=round($conn->row['gdsaw_ea_target'],2);
	
				$lg_pcs_target[]=$conn->row['lg_pcs_target'];
				$lg_ea_target[]=round($conn->row['lg_ea_target'],2);
	
				$be_pcs_target[]=$conn->row['be_pcs_target'];
				$be_ea_target[]=round($conn->row['be_ea_target'],2);
				
				$fe_pcs_target[]=$conn->row['fe_pcs_target'];
				$fe_ea_target[]=round($conn->row['fe_ea_target'],2);

			}


			$ans = array(

				"date_target"=>$date,

				"rw_pcs_target"=>$rw_pcs_target,
				"rw_ea_target"=>$rw_ea_target,

				"gdsaw_pcs_target"=>$gdsaw_pcs_target,
				"gdsaw_ea_target"=>$gdsaw_ea_target,
	
				"lg_pcs_target"=>$lg_pcs_target,
				"lg_ea_target"=>$lg_ea_target,
	
				"be_pcs_target"=>$be_pcs_target,
				"be_ea_target"=>$be_ea_target,
				
				"fe_pcs_target"=>$fe_pcs_target,
				"fe_ea_target"=>$fe_ea_target,

				"rw_pcs_target"=>$rw_pcs_target,
				"rw_ea_target"=>$rw_ea_target			

			);

			return $ans;
			
	}

	//當前move date撈出
	function move_now($date1,$date2){

		$date = $date1." 07:15:00";
		$now = date("Y-m-d H:i:s");

		$conn = new create_conn;
		$conn->conn('move');

		$sql = "SELECT * from move_now where `Checkout Time` between '$date' and '$now'";

		$conn->result($sql);

		$Station=[];
		$下線類別=[];			
		$重工片數=[];
		$總完工數量=[];
		$date=[];
		$班別=[];

		while($conn->get_row()){

			$Station[] = $conn->row['Station'];
			$下線類別[] = $conn->row['下線類別'];
			$重工片數[] = $conn->row['重工片數'];
			$總完工數量[] = $conn->row['總完工數量'];
			$date[] = $conn->row['Checkout Time'];		
		

		}			
		
		$ans=array(
			
			"Station"=>$Station,
			"下線類別"=>$下線類別,
			"重工片數"=>$重工片數,
			"總完工數量"=>$總完工數量,
			"date_move"=>$date
		

		);
			

		return $ans;
	}	

	//每日move data撈出(分AB班)
	function move_day($date1,$date2){
			$conn = new create_conn;
			$conn->conn('move');

			$sql = "SELECT * from move_history where CheckoutTime between '$date1' and '$date2'";
		
			$conn->result($sql);

			$Station=[];
			$下線類別=[];			
			$重工片數=[];
			$總完工數量=[];
			$date=[];
			$班別=[];

			while($conn->get_row()){

				$Station[] = $conn->row['Station'];
				$下線類別[] = $conn->row['下線類別'];
				$重工片數[] = $conn->row['重工片數'];
				$總完工數量[] = $conn->row['總完工數量'];
				$date[] = $conn->row['CheckoutTime'];		
				$班別[] = $conn->row['班別'];

			}			
			
			$ans=array(
				
				"Station"=>$Station,
				"下線類別"=>$下線類別,
				"重工片數"=>$重工片數,
				"總完工數量"=>$總完工數量,
				"date_move"=>$date,
				"班別"=>$班別

			);
				

			return $ans;
		}
}