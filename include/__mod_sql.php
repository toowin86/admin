<?php
 if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 

$not_tbl=array('CHARACTER_SETS','CLIENT_STATISTICS','COLLATIONS','COLLATION_CHARACTER_SET_APPLICABILITY','COLUMNS','COLUMN_PRIVILEGES','INDEX_STATISTICS','ENGINES','EVENTS','FILES','GLOBAL_STATUS','GLOBAL_TEMPORARY_TABLES','GLOBAL_VARIABLES','KEY_COLUMN_USAGE','OPTIMIZER_TRACE','PARAMETERS','PARTITIONS','PLUGINS','PROCESSLIST','PROFILING','REFERENTIAL_CONSTRAINTS','ROUTINES','SCHEMATA','SCHEMA_PRIVILEGES','SESSION_STATUS','SESSION_VARIABLES','STATISTICS','TABLES','TABLESPACES','TABLE_CONSTRAINTS','TABLE_PRIVILEGES','TABLE_STATISTICS','TEMPORARY_TABLES','THREAD_STATISTICS','TRIGGERS','USER_PRIVILEGES','USER_STATISTICS','VIEWS','INNODB_CMP','INNODB_CMP_RESET','INNODB_SYS_DATAFILES','XTRADB_READ_VIEW','INNODB_SYS_TABLESTATS','XTRADB_RSEG','INNODB_BUFFER_PAGE','INNODB_TRX','INNODB_CMP_PER_INDEX','INNODB_METRICS','INNODB_FT_DELETED','INNODB_LOCKS','INNODB_LOCK_WAITS','XTRADB_INTERNAL_HASH_TABLES','INNODB_TABLESPACES_ENCRYPTION','INNODB_CMPMEM_RESET','INNODB_SYS_FIELDS','XTRADB_ZIP_DICT','INNODB_TABLESPACES_SCRUBBING','INNODB_TEMP_TABLE_INFO','INNODB_FT_INDEX_TABLE','INNODB_CMPMEM','INNODB_SYS_TABLESPACES','INNODB_CMP_PER_INDEX_RESET','INNODB_SYS_FOREIGN_COLS','INNODB_FT_INDEX_CACHE','INNODB_BUFFER_POOL_STATS','INNODB_FT_BEING_DELETED','INNODB_SYS_FOREIGN','INNODB_BUFFER_PAGE_LRU','INNODB_FT_DEFAULT_STOPWORD','INNODB_SYS_TABLES','INNODB_SYS_COLUMNS','INNODB_FT_CONFIG','XTRADB_ZIP_DICT_COLS','INNODB_SYS_INDEXES','INNODB_SYS_VIRTUAL','INNODB_CHANGED_PAGES');


    $inc_arr=array();
    $sql="SELECT TABLE_NAME FROM INFORMATION_SCHEMA.TABLES
            ";
    $res = mysql_query($sql) or die(mysql_error().'<br>'.$sql);
    while ($row = mysql_fetch_array($res)) {
        $inc_arr[]=$row[0];
    }

    ?>
    <div class="sql_help">
         <a data-val="SELECT * FROM `tbl`  WHERE id>'0'" class="sql_help_id" href="#" >SELECT</a>,
         <a data-val="DELETE FROM `tbl`  WHERE id>'0'"  class="sql_help_id" href="#">DELETE</a>, 
         <a data-val="UPDATE `tbl` SET id=id  WHERE id>'0'" class="sql_help_id" href="#">UPDATE</a>
    </div>
    <textarea class="sql_" placeholder="Введите sql" style="width: 100%; height: 200px;"></textarea>
    <input type="button" class="sql_start"  value="Выполнить" />
    <hr />
    <div class="sql_res"></div>
    <h2>Таблицы</h2>
    <ul>
    <?php

    foreach($inc_arr as $key => $val){
        if (!in_array($val,$not_tbl)){
        ?>
        <li class="sql_help_tbl" style="cursor: pointer;"><?=$val;?></li>
        <?php
        }
    }
    ?>
    </ul>
    <?php

?>