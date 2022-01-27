<?php
//use DB;

//$chatMsgLimit = 5;

function chatMsgLimit(){
	return 2;
}

function pr($data=array()){
    echo '<pre>';
    print_r($data);
    echo '</pre>';

}
function prd($data=array()){
    echo '<pre>';
    print_r($data);
    echo '</pre>';
    die;

}
function echo_die($string=array()){
    echo $string; die;

}

function generateToken($num){
	return str_random($num);
}

/* Common function */


function randomNumber($qtd){
	$Caracteres = '0123456789'; 
	$QuantidadeCaracteres = strlen($Caracteres); 
	$QuantidadeCaracteres--; 

	$ransom_num=NULL; 
	for($x=1;$x<=$qtd;$x++){ 
		$Posicao = rand(0,$QuantidadeCaracteres); 
		$ransom_num .= substr($Caracteres,$Posicao,1); 
	} 

	return $ransom_num;
}


function dateTimeInterval($data){
	$result = '';
	$datetime1 = new DateTime($data);
		$datetime2 = new DateTime();
		$interval = $datetime1->diff($datetime2);
		
		//prd($interval);

		$year = $interval->format('%y');
		$month = $interval->format('%m');
		$days = $interval->format('%d');
		$hours = $interval->format('%h');
		$minutes = $interval->format('%i');
		$seconds = $interval->format('%s');


		if($year > 0){
			$result = $year.' Year';
			if($year > 1){ $result = $result.'s'; }
		}
		elseif($month > 0){
			$result = $month.' Month';
			if($month > 1){ $result = $result.'s'; }
		}
		elseif($days > 0){
			$result = $days.' Day';
			if($days > 1){ $result = $result.'s'; }
		}
		elseif($hours > 0){
			$result = $hours.' Hour';
			if($hours > 1){ $result = $result.'s'; }
		}
		elseif($minutes > 0){
			$result = $minutes.' Minute';
			if($result > 1){ $result = $result.'s'; }
		}
		elseif($seconds > 0){
			$result = $seconds.' Second';
			if($seconds > 1){ $result = $result.'s'; }
		}

		return $result;
	}

    function slugify($text)
    {
        // echo $text; die;
        // replace non letter or digits by -
        $text = preg_replace ( '~[^\pL\d]+~u', '-', $text );
        
        // transliterate
        $text = iconv ( 'utf-8', 'us-ascii//TRANSLIT', $text );
        
        // remove unwanted characters
        $text = preg_replace ( '~[^-\w]+~', '', $text );
        
        // trim
        $text = trim ( $text, '-' );
        
        // remove duplicate -
        $text = preg_replace ( '~-+~', '-', $text );
        
        // lowercase
        $text = strtolower ( $text );
        // echo $text; die;
        if (empty ( $text )) {
            // return 'n-a';
        }
        // echo $text; die;
        return $text;
    }


/* End - Common function */

?>