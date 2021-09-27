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
                            array_push($ary,geraNovoCNPJValido($ary));    
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


function geraNovoCNPJValido($aryValores) {
    
    $blnTentaGerarCNPJ = true;
    
    while($blnTentaGerarCNPJ) {
        
        $auxNovoCNPJ = geraCNPJ();
        if(validaCNPJ($auxNovoCNPJ)) {
        //if(true) {
            
            if(array_search($auxNovoCNPJ, $aryValores,true)) {
                $blnTentaGerarCNPJ = true;
            } else {
                $blnTentaGerarCNPJ = false;
            }
            
        } 
    }
    unset($blnTentaGerarCNPJ);
    
    return $auxNovoCNPJ;
    
}

function geraCNPJ() {
    
    $auxCNPJ = "";
    $dv1 = "";
    $dv2 = "";
    
    //Gerando radical do CNPJ
    for($a = 0; $a < 8; $a++) {
        $auxCNPJ = $auxCNPJ.rand(0,9);
    }
    $auxCNPJ = $auxCNPJ.'0001';
        
    //Calculado DV1
    $auxDV1 = 0;
    $auxMult = 5;
     
    for($a = 0; $a < 12; $a++) {
        $auxDV1 = $auxDV1+($auxCNPJ[$a]*$auxMult);
        if($auxMult == 2) {
            $auxMult = 9;
        } else {
            $auxMult--;
        }
    }
    unset($auxMult);
    
    $auxRestoDV1 = $auxDV1%11;
    
    if($auxRestoDV1 < 2) {
        $dv1 = 0;
    } else {
        $dv1 = 11 - $auxRestoDV1;
    }
    unset($auxRestoDV1,$auxDV1);
    
    $auxCNPJ = $auxCNPJ.$dv1;
        
    //Calculado DV2
    $auxDV2 = 0;
    $auxMult = 6;
    for($a = 0; $a < 13; $a++) {
        $auxDV2 = $auxDV2+($auxCNPJ[$a]*$auxMult);
        if($auxMult == 2) {
            $auxMult = 9;
        } else {
            $auxMult--;
        }
    }
    unset($auxMult);
    
    $auxRestoDV2 = $auxDV2%11;
    
    if($auxRestoDV2 < 2) {
        $dv2 = 0;
    } else {
        $dv2 = 11 - $auxRestoDV2;
    }
    unset($auxRestoDV2,$auxDV2);
    
    $auxCNPJ = $auxCNPJ.$dv2;
 
    unset($a,$dv1,$dv2);
    
    return $auxCNPJ;
    unset($auxCNPJ);
}



function validaCNPJ($cnpj = null) {
    
    // Verifica se um número foi informado
    if(empty($cnpj)) {
        return false;
    }
    
    // Elimina possivel mascara
    $cnpj = preg_replace("/[^0-9]/", "", $cnpj);
    $cnpj = str_pad($cnpj, 14, '0', STR_PAD_LEFT);
    
    // Verifica se o numero de digitos informados é igual a 11
    if (strlen($cnpj) != 14) {
        return false;
    }
    
    // Verifica se nenhuma das sequências invalidas abaixo
    // foi digitada. Caso afirmativo, retorna falso
    else if ($cnpj == '00000000000000' ||
        $cnpj == '11111111111111' ||
        $cnpj == '22222222222222' ||
        $cnpj == '33333333333333' ||
        $cnpj == '44444444444444' ||
        $cnpj == '55555555555555' ||
        $cnpj == '66666666666666' ||
        $cnpj == '77777777777777' ||
        $cnpj == '88888888888888' ||
        $cnpj == '99999999999999') {
            return false;
            
            // Calcula os digitos verificadores para verificar se o
            // CNPJ é válido
        } else {
            
            $j = 5;
            $k = 6;
            $soma1 = 0;
            $soma2 = 0;
            
            for ($i = 0; $i < 13; $i++) {
                
                $j = $j == 1 ? 9 : $j;
                $k = $k == 1 ? 9 : $k;
                
                $soma2 += ($cnpj{$i} * $k);
                
                if ($i < 12) {
                    $soma1 += ($cnpj{$i} * $j);
                }
                
                $k--;
                $j--;
                
            }
            
            $digito1 = $soma1 % 11 < 2 ? 0 : 11 - $soma1 % 11;
            $digito2 = $soma2 % 11 < 2 ? 0 : 11 - $soma2 % 11;
            
            return (($cnpj{12} == $digito1) and ($cnpj{13} == $digito2));
            
        }
}

if(isset($JSONStandard)){ unset($JSONStandard);}

if(isset($json_str)){ unset($json_str);}
if(isset($json_obj)){ unset($json_obj);}
if(isset($result)){ unset($result);}

echo $json_output;

?>
