<?php
require 'include/headers_api_php.php';
require 'include/std_messages_api_php.php';


# Get JSON as a string
$json_str = file_get_contents('php://input');

# Get as an object
$json_obj = json_decode($json_str, true);

if(count($json_obj) < 2) {
    $json_output = $JSONStandard[3];
} else {
    
    if(!isset($json_obj["authKey"]) || !isset($json_obj["param"])) {
        $json_output = $JSONStandard[3];
    } else {
        
        if(isset($json_obj["authKey"])) {
            //Verifica a permissao de uso
            //Como eh publica, todos podem usar
            if(true) {
                
                if($json_obj["param"][0] != "" && $json_obj["param"][0] != 0 && is_numeric($json_obj["param"][0])) {
                    //Executa o codigo da API
                    if(round($json_obj["param"][0]) <= 100) {
                        
                        $ary = [];

                        for($i = 0; $i < round($json_obj["param"][0]); $i++) {
                            array_push($ary,geraNovoCPFValido($ary));    
                        }
                        unset($i);
                        
                        $strResult = "[";
                        $strSeparador = "";
                        for($i = 0; $i < count($ary); $i++) {
                            $strResult = $strResult.$strSeparador.'"'.$ary[$i].'"';
                            ($i == 0 ? $strSeparador = "," : true);
                        }
                        unset($i,$strSeparador,$ary);
                        $strResult = $strResult."]";
                        
                        $json_output = str_replace('__RESULT__', $strResult , $JSONStandard[0]);
                        
                        unset($strResult);
                    } else {
                        //server error
                        $json_output = str_replace('__RESULT__', '"Qtd de elementos maior que o limite permitido"', $JSONStandard[1]);
                    }
                    
                    
                } else {
                    //Server error
                    $json_output = str_replace('__RESULT__', '{}', $JSONStandard[1]);
                }
                
            } else {
                //Forbidden
                $json_output = $JSONStandard[2];
            }
            
        } else {
            //Forbidden
            $json_output = $JSONStandard[2];
        }
        
    }
}


function geraNovoCPFValido($aryValores) {
    
    $blnTentaGerarCPF = true;
    
    while($blnTentaGerarCPF) {
        
        $auxNovoCPF = geraCPF();
        if(validaCPF($auxNovoCPF)) {
            
            if(array_search($auxNovoCPF, $aryValores,true)) {
                $blnTentaGerarCPF = true;
            } else {
                $blnTentaGerarCPF = false;
            }
            
        } 
    }
    unset($blnTentaGerarCPF);
    
    return $auxNovoCPF;
    
}

function geraCPF() {
    
    $auxCPF = "";
    $dv1 = "";
    $dv2 = "";
    
    //Gerando radical do CPF
    for($a = 0; $a < 9; $a++) {
        $auxCPF = $auxCPF.rand(0,9);
    }
  
    //Calculado DV1
    $auxDV1 = 0;
    $auxMult = 10;
     
    for($a = 0; $a < 9; $a++) {
        $auxDV1 = $auxDV1+($auxCPF[$a]*$auxMult);
        $auxMult--;
    }
    unset($auxMult);
    
    $auxRestoDV1 = $auxDV1%11;
    
    if($auxRestoDV1 < 2) {
        $dv1 = 0;
    } else {
        $dv1 = 11 - $auxRestoDV1;
    }
    unset($auxRestoDV1,$auxDV1);
    
    $auxCPF = $auxCPF.$dv1;
        
    //Calculado DV2
    $auxDV2 = 0;
    $auxMult = 11;
    for($a = 0; $a < 10; $a++) {
        $auxDV2 = $auxDV2+($auxCPF[$a]*$auxMult);
        $auxMult--;
    }
    unset($auxMult);
    
    $auxRestoDV2 = $auxDV2%11;
    
    if($auxRestoDV2 < 2) {
        $dv2 = 0;
    } else {
        $dv2 = 11 - $auxRestoDV2;
    }
    unset($auxRestoDV2,$auxDV2);
    
    $auxCPF = $auxCPF.$dv2;
 
    unset($a,$dv1,$dv2);
    
    return $auxCPF;
    unset($auxCPF);
}


function validaCPF($cpf = null) {
    
    // Verifica se um número foi informado
    if(empty($cpf)) {
        return false;
    }
    
    // Elimina possivel mascara
    $cpf = preg_replace("/[^0-9]/", "", $cpf);
    $cpf = str_pad($cpf, 11, '0', STR_PAD_LEFT);
    
    // Verifica se o numero de digitos informados é igual a 11
    if (strlen($cpf) != 11) {
        return false;
    }
    // Verifica se nenhuma das sequências invalidas abaixo
    // foi digitada. Caso afirmativo, retorna falso
    else if ($cpf == '00000000000' ||
        $cpf == '11111111111' ||
        $cpf == '22222222222' ||
        $cpf == '33333333333' ||
        $cpf == '44444444444' ||
        $cpf == '55555555555' ||
        $cpf == '66666666666' ||
        $cpf == '77777777777' ||
        $cpf == '88888888888' ||
        $cpf == '99999999999') {
            return false;
            // Calcula os digitos verificadores para verificar se o
            // CPF é válido
        } else {
            
            for ($t = 9; $t < 11; $t++) {
                
                for ($d = 0, $c = 0; $c < $t; $c++) {
                    $d += $cpf{$c} * (($t + 1) - $c);
                }
                $d = ((10 * $d) % 11) % 10;
                if ($cpf{$c} != $d) {
                    return false;
                }
            }
            
            return true;
        }
}

if(isset($JSONStandard)){ unset($JSONStandard);}

if(isset($json_str)){ unset($json_str);}
if(isset($json_obj)){ unset($json_obj);}
if(isset($result)){ unset($result);}

echo $json_output;

?>
