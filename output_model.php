<?php

class output_model{

	function __construct() {
		require_once(ROOT.'connect.php');   
	}

	//每日target data撈出
	function target_day($date1,$date2){
		$conn = new create_conn;
		$conn->conn('ie_parameter');
			//a的foutput
			$sql = "SELECT * from daily_target where (date between '$date1' and '$date2')";

			$conn->result($sql);

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

			$rw_pcs_target_acc=[];
			$rw_ea_target_acc=[];

			$gdsaw_pcs_target_acc=[];
			$gdsaw_ea_target_acc=[];

			$lg_pcs_target_acc=[];
			$lg_ea_target_acc=[];

			$be_pcs_target_acc=[];
			$be_ea_target_acc=[];
			
			$fe_pcs_target_acc=[];
			$fe_ea_target_acc=[];
///
			$rw_pcs_target_empty=[];
			$rw_ea_target_empty=[];

			$gdsaw_pcs_target_empty=[];
			$gdsaw_ea_target_empty=[];

			$lg_pcs_target_empty=[];
			$lg_ea_target_empty=[];			

			$be_pcs_target_empty=[];
			$be_ea_target_empty=[];			

			$fe_pcs_target_empty=[];
			$fe_ea_target_empty=[];			

			//將ARR累加結果+目前的值
			foreach ($rw_pcs_target as $AAA) {
				$rw_pcs_target_empty[]=$AAA;
				$rw_pcs_target_acc[]= ( $rw_pcs_target_acc==null?$AAA:($rw_pcs_target_acc[(count($rw_pcs_target_acc)-1)])+$AAA );								
			}
			foreach ($rw_ea_target as $AAA) {
				$rw_ea_target_empty[]=$AAA;
				$rw_ea_target_acc[]= ( $rw_ea_target_acc==null?$AAA:($rw_ea_target_acc[(count($rw_ea_target_acc)-1)])+$AAA );								
			}

			foreach ($gdsaw_pcs_target as $AAA) {
				$gdsaw_pcs_target_empty[]=$AAA;
				$gdsaw_pcs_target_acc[]= ( $gdsaw_pcs_target_acc==null?$AAA:($gdsaw_pcs_target_acc[(count($gdsaw_pcs_target_acc)-1)])+$AAA );								
			}
			foreach ($gdsaw_ea_target as $AAA) {
				$gdsaw_ea_target_empty[]=$AAA;
				$gdsaw_ea_target_acc[]= ( $gdsaw_ea_target_acc==null?$AAA:($gdsaw_ea_target_acc[(count($gdsaw_ea_target_acc)-1)])+$AAA );								
			}

			foreach ($lg_pcs_target as $AAA) {
				$lg_pcs_target_empty[]=$AAA;
				$lg_pcs_target_acc[]= ( $lg_pcs_target_acc==null?$AAA:($lg_pcs_target_acc[(count($lg_pcs_target_acc)-1)])+$AAA );								
			}
			foreach ($lg_ea_target as $AAA) {
				$lg_ea_target_empty[]=$AAA;
				$lg_ea_target_acc[]= ( $lg_ea_target_acc==null?$AAA:($lg_ea_target_acc[(count($lg_ea_target_acc)-1)])+$AAA );								
			}		
			
			foreach ($be_pcs_target as $AAA) {
				$be_pcs_target_empty[]=$AAA;
				$be_pcs_target_acc[]= ( $be_pcs_target_acc==null?$AAA:($be_pcs_target_acc[(count($be_pcs_target_acc)-1)])+$AAA );								
			}
			foreach ($be_ea_target as $AAA) {
				$be_ea_target_empty[]=$AAA;
				$be_ea_target_acc[]= ( $be_ea_target_acc==null?$AAA:($be_ea_target_acc[(count($be_ea_target_acc)-1)])+$AAA );								
			}			

			foreach ($fe_pcs_target as $AAA) {
				$fe_pcs_target_empty[]=$AAA;
				$fe_pcs_target_acc[]= ( $fe_pcs_target_acc==null?$AAA:($fe_pcs_target_acc[(count($fe_pcs_target_acc)-1)])+$AAA );								
			}
			foreach ($fe_ea_target as $AAA) {
				$fe_ea_target_empty[]=$AAA;
				$fe_ea_target_acc[]= ( $fe_ea_target_acc==null?$AAA:($fe_ea_target_acc[(count($fe_ea_target_acc)-1)])+$AAA );								
			}	

			$ans = array(

				"date"=>$date,

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
				"rw_ea_target"=>$rw_ea_target,

				"rw_pcs_target_acc"=>$rw_pcs_target_acc,
				"rw_ea_target_acc"=>$rw_ea_target_acc,

				"gdsaw_pcs_target_acc"=>$gdsaw_pcs_target_acc,
				"gdsaw_ea_target_acc"=>$gdsaw_ea_target_acc,
	
				"lg_pcs_target_acc"=>$lg_pcs_target_acc,
				"lg_ea_target_acc"=>$lg_ea_target_acc,
	
				"be_pcs_target_acc"=>$be_pcs_target_acc,
				"be_ea_target_acc"=>$be_ea_target_acc,
				
				"fe_pcs_target_acc"=>$fe_pcs_target_acc,
				"fe_ea_target_acc"=>$fe_ea_target_acc				

			);

			return $ans;
			
	}

	//每日output data撈出
	function output_day($process){
		$conn = new create_conn;
		$conn->conn('output');

			//a的foutput
			$sql = "SELECT * from a_tt_history where mo_worktype='$process'";
			$conn->result($sql);

			$a_wf_output=[];
			$a_fm_output=[];			
			$a_bad_ea_output=[];
			$a_ea_output=[];
			$a_date=[];
			$a_mo_online=[];

			while($conn->get_row()){

				$a_mo_online[] = $conn->row['mo_online'];
				$a_fm_output[] = $conn->row['fm_output'];
				$a_wf_output[] = $conn->row['wf_output'];
				$a_bad_ea_output[] = $conn->row['bad_ea_output'];
				$a_ea_output[] = $conn->row['ea_output'];
				$a_date[] = $conn->row['date'];			

			}			
				
			//b的output
			$sql = "SELECT * from b_tt_history where mo_worktype='$process'";
			$conn->result($sql);

			$b_wf_output=[];
			$b_fm_output=[];			
			$b_bad_ea_output=[];
			$b_ea_output=[];
			$b_date=[];
			$b_mo_online=[];

			while($conn->get_row()){

				$b_mo_online[] = $conn->row['mo_online'];
				$b_fm_output[] = $conn->row['fm_output'];
				$b_wf_output[] = $conn->row['wf_output'];
				$b_bad_ea_output[] = $conn->row['bad_ea_output'];
				$b_ea_output[] = $conn->row['ea_output'];
				$b_date[] = $conn->row['date'];			

			}			
							

			/////////output day caculate

			$a_date_arr = array_values(array_unique($a_date));
			$b_date_arr = array_values(array_unique($b_date));

			$date_arr = array_merge($a_date_arr,$b_date_arr);

			$date_arr = array_values(array_unique($date_arr));

			for($i=0;$i<count($date_arr);$i++){

			$str = $date_arr[$i];

			${$str}["a_fm_output"]=0;
			${$str}["a_wf_output"]=0;  
			${$str}["a_ea_output"]=0;
			${$str}["a_fm_output_acc"]=0;
			${$str}["a_wf_output_acc"]=0;  
			${$str}["a_ea_output_acc"]=0;   
			${$str}["a_bad_ea_output"]=0;

			${$str}["a_good_rate"]=0;

			${$str}["b_fm_output"]=0;
			${$str}["b_wf_output"]=0;  
			${$str}["b_ea_output"]=0;
			${$str}["b_fm_output_acc"]=0;
			${$str}["b_wf_output_acc"]=0;  
			${$str}["b_ea_output_acc"]=0;  
			${$str}["b_bad_ea_output"]=0;  

			${$str}["b_good_rate"]=0;  
			}

			for($i=0;$i<count($date_arr);$i++){

				$str = $date_arr[$i];
				
				for($j=0;$j<count($a_date);$j++){

				if($str==$a_date[$j]){

					if($a_mo_online[$j]<>'實驗批' && $a_mo_online[$j]<>'REWORK'){
					
						${$str}['a_fm_output'] =  ${$str}['a_fm_output'] + $a_fm_output[$j];
						${$str}['a_wf_output'] =  ${$str}['a_wf_output'] + $a_wf_output[$j];
						${$str}['a_ea_output'] =  ${$str}['a_ea_output'] + round($a_ea_output[$j]/1000000,2);
						${$str}['a_bad_ea_output'] =  ${$str}['a_bad_ea_output'] + round($a_bad_ea_output[$j]/1000000,2);              
					

					}else{

						${$str}['a_ea_output'] =  ${$str}['a_ea_output'] + round($a_ea_output[$j]/1000000,2);
						${$str}['a_bad_ea_output'] =  ${$str}['a_bad_ea_output'] + round($a_bad_ea_output[$j]/1000000,2);

					}        
					if(${$str}['a_ea_output']>0){
					${$str}['a_good_rate'] = round(1 - (${$str}['a_bad_ea_output'] / ${$str}['a_ea_output']),2)*100;
					}
				}
				}


				////

				for($j=0;$j<count($b_date);$j++){

				if($str==$b_date[$j]){

					if($b_mo_online[$j]<>'實驗批' && $b_mo_online[$j]<>'REWORK'){
					
						${$str}['b_fm_output'] =  ${$str}['b_fm_output'] + $b_fm_output[$j];
						${$str}['b_wf_output'] =  ${$str}['b_wf_output'] + $b_wf_output[$j];

						${$str}['b_ea_output'] =  ${$str}['b_ea_output'] + round($b_ea_output[$j]/1000000,2);
						${$str}['b_bad_ea_output'] =  ${$str}['b_bad_ea_output'] + round($b_bad_ea_output[$j]/1000000,2);              
						

					}else{

						${$str}['b_ea_output'] =  ${$str}['b_ea_output'] + round($b_ea_output[$j]/1000000,2);
						${$str}['b_bad_ea_output'] =  ${$str}['b_bad_ea_output'] + round($b_bad_ea_output[$j]/1000000,2);

					}        

					if(${$str}['b_ea_output']>0){
					${$str}['b_good_rate'] = round(1 - (${$str}['b_bad_ea_output'] / ${$str}['b_ea_output']),2)*100;
					}

				}
				}    
			}
			//target output///撈取target
					
			sort($date_arr);
			$l = count($date_arr)-1;

			$target_day = $this->target_day($date_arr[0],$date_arr[$l]);

			$date_target=$target_day['date'];
			$rw_pcs_target=$target_day['rw_pcs_target'];
			$rw_ea_target=$target_day['rw_ea_target'];
			$gdsaw_pcs_target=$target_day['gdsaw_pcs_target'];
			$gdsaw_ea_target=$target_day['gdsaw_ea_target'];
			$lg_pcs_target=$target_day['lg_pcs_target'];
			$lg_ea_target=$target_day['lg_ea_target'];
			$fe_pcs_target=$target_day['fe_pcs_target'];
			$fe_ea_target=$target_day['fe_ea_target'];
			$be_pcs_target=$target_day['be_pcs_target'];
			$be_ea_target=$target_day['be_ea_target'];
		
			$rw_pcs_target_acc=$target_day['rw_pcs_target_acc'];
			$rw_ea_target_acc=$target_day['rw_ea_target_acc'];
		
			$gdsaw_pcs_target_acc=$target_day['gdsaw_pcs_target_acc'];
			$gdsaw_ea_target_acc=$target_day['gdsaw_ea_target_acc'];
			$lg_pcs_target_acc=$target_day['lg_pcs_target_acc'];
			$lg_ea_target_acc=$target_day['lg_ea_target_acc'];
			$fe_pcs_target_acc=$target_day['fe_pcs_target_acc'];
			$fe_ea_target_acc=$target_day['fe_ea_target_acc'];
			$be_pcs_target_acc=$target_day['be_pcs_target_acc'];
			$be_ea_target_acc=$target_day['be_ea_target_acc'];
		
		//target值丟進arr  
		  for($i=0;$i<count($date_arr);$i++){
		
			$str = $date_arr[$i];
			
			for($j=0;$j<count($date_target);$j++){
		
				if($str==$date_target[$j]){
		
					${$str}["rw_pcs_target"] = $rw_pcs_target[$j];
					${$str}["rw_ea_target"] = $rw_ea_target[$j];          
					
					${$str}["gdsaw_pcs_target"] = $gdsaw_pcs_target[$j];
					${$str}["gdsaw_ea_target"] = $gdsaw_ea_target[$j];  
					
					${$str}["lg_pcs_target"] = $lg_pcs_target[$j];
					${$str}["lg_ea_target"] = $lg_ea_target[$j];       
					
					${$str}["fe_pcs_target"] = $fe_pcs_target[$j];
					${$str}["fe_ea_target"] = $fe_ea_target[$j];  
		
					${$str}["be_pcs_target"] = $be_pcs_target[$j];
					${$str}["be_ea_target"] = $be_ea_target[$j];      
					
					${$str}["tt_fm_output"] = ${$str}['a_fm_output'] + ${$str}['b_fm_output'];
					${$str}["tt_wf_output"] = ${$str}['a_wf_output'] + ${$str}['b_wf_output'];
					${$str}["tt_ea_output"] = ${$str}['a_ea_output'] + ${$str}['b_ea_output'];

				}
			}
		  }

				//date攤成字串
				$date=[];
				foreach($date_arr as $dd){
					$dd = '"'.$dd.'"';
					array_push($date,$dd);
					
				}

				//各output攤成字串，先行丟進arr

				$a_fm_output=[];
				$b_fm_output=[];

				$a_ea_output=[];
				$b_ea_output=[];

				$a_wf_output=[];
				$b_wf_output=[];

				$a_bad_ea_output=[];
				$b_bad_ea_output=[];

				$tt_fm_output=[];
				$tt_wf_output=[];
				$tt_ea_output=[];

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

				$fm_cvp_ratio=[];
				$wf_cvp_ratio=[];
				$ea_cvp_ratio=[];


				for($i=0;$i<count($date_arr);$i++){

				
					$str = $date_arr[$i];

					if(isset($a_good_rate)==false){

					$a_good_rate=[];
					
					}

					if(isset($b_good_rate)==false){

					$b_good_rate=[];
					
					}

					array_push($a_fm_output,${$str}["a_fm_output"]);
					array_push($a_wf_output,${$str}["a_wf_output"]);    
					array_push($a_ea_output,${$str}["a_ea_output"]);       
					array_push($a_bad_ea_output,${$str}["a_bad_ea_output"]); 
					
					array_push($a_good_rate,${$str}["a_good_rate"]);    

					array_push($b_fm_output,${$str}["b_fm_output"]);
					array_push($b_wf_output,${$str}["b_wf_output"]);    
					array_push($b_ea_output,${$str}["b_ea_output"]);       
					array_push($b_bad_ea_output,${$str}["b_bad_ea_output"]);                       

					array_push($b_good_rate,${$str}["b_good_rate"]);         

					if(isset(${$str}["rw_pcs_target"])){

						array_push($rw_pcs_target,${$str}["rw_pcs_target"]);
						array_push($rw_ea_target,${$str}["rw_ea_target"]);    

						array_push($gdsaw_pcs_target,${$str}["gdsaw_pcs_target"]);
						array_push($gdsaw_ea_target,${$str}["gdsaw_ea_target"]);    

						array_push($lg_pcs_target,${$str}["lg_pcs_target"]);
						array_push($lg_ea_target,${$str}["lg_ea_target"]);      

						array_push($fe_pcs_target,${$str}["fe_pcs_target"]);
						array_push($fe_ea_target,${$str}["fe_ea_target"]);   

						array_push($be_pcs_target,${$str}["be_pcs_target"]);
						array_push($be_ea_target,${$str}["be_ea_target"]);       

						array_push($tt_fm_output,${$str}["tt_fm_output"]);
						array_push($tt_wf_output,${$str}["tt_wf_output"]);       
						array_push($tt_ea_output,${$str}["tt_ea_output"]);     
						

					}      

				}


				//output累加
					$a_fm_output_acc=[];
					$a_wf_output_acc=[];			
					$a_ea_output_acc=[];
					$b_fm_output_acc=[];
					$b_wf_output_acc=[];			
					$b_ea_output_acc=[];
					///
					$a_fm_output_empty=[];
					$a_wf_output_empty=[];			
					$a_ea_output_empty=[];
					$b_fm_output_empty=[];
					$b_wf_output_empty=[];			
					$b_ea_output_empty=[];

					//將ARR累加結果+目前的值
					foreach ($a_fm_output as $AAA) {
					$a_fm_output_empty[]=$AAA;
					$a_fm_output_acc[]= ( $a_fm_output_acc==null?$AAA:($a_fm_output_acc[(count($a_fm_output_acc)-1)])+$AAA );								
					}
					foreach ($a_wf_output as $AAA) {
					$a_wf_output_empty[]=$AAA;
					$a_wf_output_acc[]= ( $a_wf_output_acc==null?$AAA:($a_wf_output_acc[(count($a_wf_output_acc)-1)])+$AAA );								
					}    
					foreach ($a_ea_output as $AAA) {
					
					$a_ea_output_empty[]=$AAA;
					$a_ea_output_acc[]= ( $a_ea_output_acc==null?$AAA:($a_ea_output_acc[(count($a_ea_output_acc)-1)])+$AAA );								
					}    
					foreach ($b_fm_output as $AAA) {
					$b_fm_output_empty[]=$AAA;
					$b_fm_output_acc[]= ( $b_fm_output_acc==null?$AAA:($b_fm_output_acc[(count($b_fm_output_acc)-1)])+$AAA );								
					}
					foreach ($b_wf_output as $AAA) {
					$b_wf_output_empty[]=$AAA;
					$b_wf_output_acc[]= ( $b_wf_output_acc==null?$AAA:($b_wf_output_acc[(count($b_wf_output_acc)-1)])+$AAA );								
					}    
					foreach ($b_ea_output as $AAA) {
					$b_ea_output_empty[]=$AAA;
					$b_ea_output_acc[]= ( $b_ea_output_acc==null?$AAA:($b_ea_output_acc[(count($b_ea_output_acc)-1)])+$AAA );								
					}       

				///output累加結束

				//a_b_output累加合計

				$tt_fm_output_acc=[];
				$tt_ea_output_acc=[];
				$tt_wf_output_acc=[];


				for($i=0;$i<count($a_fm_output_acc);$i++){

				array_push($tt_fm_output_acc,$a_fm_output_acc[$i]+$b_fm_output_acc[$i]);
				}
				for($i=0;$i<count($a_wf_output_acc);$i++){

				array_push($tt_wf_output_acc,$a_wf_output_acc[$i]+$b_wf_output_acc[$i]);
				}
				for($i=0;$i<count($a_ea_output_acc);$i++){

				array_push($tt_ea_output_acc,$a_ea_output_acc[$i]+$b_ea_output_acc[$i]);  
				}

				//CVP RATIO計算
				//CVP RATIO = output_acc / target_cc

				$ea_cvp_ratio=[];
				$fm_cvp_ratio=[];
				$wf_cvp_ratio=[];
				$cvp_std=[];

				if($process=="RW"){

					for($i=0;$i<count($rw_ea_target_acc);$i++){

						//借殼插入
						array_push($cvp_std,100);

						if($rw_ea_target_acc[$i]>0){
						$ratio = round($tt_ea_output_acc[$i] / $rw_ea_target_acc[$i],2)*100;
						
						}else{$ratio=0;}

						array_push($ea_cvp_ratio,$ratio);
					}

					for($i=0;$i<count($rw_pcs_target_acc);$i++){

					if($rw_pcs_target_acc[$i]>0){
					$ratio = round($tt_fm_output_acc[$i] / $rw_pcs_target_acc[$i],2)*100;    
					}else{$ratio=0;}

					array_push($fm_cvp_ratio,$ratio);
					}

				}else if($process=="研磨" || $process=="切割"){

					for($i=0;$i<count($gdsaw_ea_target_acc);$i++){

						//借殼插入
						array_push($cvp_std,100);

					if($gdsaw_ea_target_acc[$i]>0){
					$ratio = round($tt_ea_output_acc[$i] / $gdsaw_ea_target_acc[$i],2)*100;    
					}else{$ratio=0;}

					array_push($ea_cvp_ratio,$ratio);
				}

				for($i=0;$i<count($gdsaw_pcs_target_acc);$i++){

					if($gdsaw_pcs_target_acc[$i]>0){
					$ratio = round($tt_wf_output_acc[$i] / $gdsaw_pcs_target_acc[$i],2)*100;    
					}else{$ratio=0;}

					array_push($wf_cvp_ratio,$ratio);
				}


				}


				//CVP RATIO計算結束

				//良率計算
				$good_rate=[];

				for($i=0;$i<max(count($a_good_rate),count($b_good_rate));$i++){

					$average = round( ($a_good_rate[$i]+$b_good_rate[$i])/2,2);
					array_push($good_rate,$average);


				}

				sort($date);
				$max_wf=max($tt_wf_output)*1.5;
				$max_fm=max($tt_fm_output)*1.5;
				$max_ea=max($tt_ea_output)*1.5;



				$ans = array(
							"date"=>$date,
							"max_wf"=>$max_wf,
							"max_fm"=>$max_fm,
							"max_ea"=>$max_ea,						
							"a_wf_output" => $a_wf_output,
							"a_fm_output" => $a_fm_output,
							"a_ea_output" => $a_ea_output,
							"a_bad_ea_output" => $a_bad_ea_output ,
							"a_good_rate" => $a_good_rate,
							"b_wf_output" => $b_wf_output,
							"b_fm_output" => $b_fm_output,
							"b_ea_output" => $b_ea_output,
							"b_bad_ea_output" => $b_bad_ea_output,
							"b_good_rate" => $b_good_rate,
							"good_rate" => $good_rate,
							"rw_pcs_target" => $rw_pcs_target,
							"rw_ea_target" => $rw_ea_target,
							"gdsaw_pcs_target" => $gdsaw_pcs_target,
							"gdsaw_ea_target" => $gdsaw_ea_target,
							"lg_pcs_target" => $lg_pcs_target,
							"lg_ea_target" => $lg_ea_target,
							"be_pcs_target" => $be_pcs_target,
							"be_ea_target" => $be_ea_target,
							"fe_pcs_target" => $fe_pcs_target,
							"fe_ea_target" => $fe_ea_target,
							"tt_wf_output" => $tt_wf_output,
							"tt_fm_output" => $tt_fm_output,
							"tt_ea_output" => $tt_ea_output,
							"wf_cvp_ratio" => $wf_cvp_ratio,
							"fm_cvp_ratio" => $fm_cvp_ratio,
							"ea_cvp_ratio" => $ea_cvp_ratio,
							"cvp_std" => $cvp_std
				);

				return $ans;

	}

}