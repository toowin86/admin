<?php
 if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<style>
    .__mod_difference_lines{font-size: 16px;margin: 0 0 0 20px;}
        .__mod_difference_lines_1_text{width: 100%; height: 220px;padding: 5px;}
        .__mod_difference_lines_2_text{width: 100%; height: 220px;padding: 5px;}
</style>
<div class="__mod_difference_lines">
    <h1>Сравнение двух текстов по строково</h1>
    <p><input type="checkbox" value="6" id="del_clone" /> <label for="del_clone"><strong>Удалить клоны</strong></label></p>
    
    <table style="width: 100%;">
        <tr>
            <td style="width: 50%;padding: 10px;">
                <textarea class="__mod_difference_lines_1_text" name="in" placeholder="Введите текст 1 для сравнения"></textarea>
            </td>
            <td style="width: 50%;padding: 10px;">
                <textarea class="__mod_difference_lines_2_text" name="out" placeholder="Введите текст 2 для сравнения"></textarea>
            </td>
        </tr>
        <tr>
            <td colspan="2">
            <div class="__mod_difference_lines_com">
                <center><span class="btn_orange __mod_difference_lines_send">Сравнить</span></center>
            </div>
            </td>
        </tr>
        <tr>
            <td class="res1">
                
            </td>
            <td class="res2">
                
            </td>
        </tr>
    </table>
</div>