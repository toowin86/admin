<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 


if (isset($table) and $table!=''){
    $needle[$table]['_coding_']['name']=$univers_code;
    $needle[$table]['_coding_']['_sql_']="ALTER TABLE `$table` DEFAULT CHARACTER SET utf8 COLLATE ".$needle[$table]['_coding_']['name'].";";
    
$old_col=' FIRST ';
foreach ($col as $col => $col_arr) {
    $needle[$table]['col'][$col]['_type_']=$col_arr['_type'];
        if (isset($col_arr['DEFAULT'])) {$needle[$table]['col'][$col]['_def_']='DEFAULT '.$col_arr['DEFAULT'];} else {$needle[$table]['col'][$col]['_def_']='';}
    $needle[$table]['col'][$col]['_coding_']=$univers_code;
    if (strstr($needle[$table]['col'][$col]['_type_'],'char')==true 
        or strstr($needle[$table]['col'][$col]['_type_'],'text')==true ) 
            {$needle[$table]['col'][$col]['_coding_']='CHARACTER SET utf8 COLLATE '.$needle[$table]['col'][$col]['_coding_'];}
    else {$needle[$table]['col'][$col]['_coding_']='';}
    //ALTER TABLE `a_admin` CHANGE `login` `login` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '11';
    $NN='NULL';
    if ($col=='id'){
        $NN='NOT NULL';
    }
    $needle[$table]['col'][$col]['_sql_upp']='ALTER TABLE `'.$table.'` CHANGE `'.$col.'` `'.$col.'` '.$needle[$table]['col'][$col]['_type_'].' '.$needle[$table]['col'][$col]['_coding_'].' '.$NN.' '.$needle[$table]['col'][$col]['_def_'].';';
    $needle[$table]['col'][$col]['_sql_ins']='ALTER TABLE `'.$table.'` ADD `'.$col.'` '.$needle[$table]['col'][$col]['_type_'].' '.$needle[$table]['col'][$col]['_coding_'].' '.$NN.' '.$needle[$table]['col'][$col]['_def_'].' '.$old_col.';';
    $old_col='AFTER `'.$col.'`';
}

unset($table,$col);
}else {echo 'no_incude';}
?>