<?php 
     if (!isset($base_name) or $base_name==''){exit();} // защита от прямого захода 
?>
<div class="s_struktura_s_tests_div_first">
<input type="hidden" value="" placeholder="hidden" class="<?=$data_['col'][$myrow[0]];?>" name="<?=$data_['col'][$myrow[0]];?>" />
<p><label><input type="checkbox" class="s_test_chk_active" name="s_test_chk_active"/> <strong>Включен тест</strong></label> <br />
<span>Вы можете создать тест и предложить варианты решения для него. <span class="btn_gray open_test_form">Развернуть</span></span></p>
<div class="s_struktura_s_tests_div" style="display: none;">
    <div class="s_struktura_s_tests_div_main">
        <div>
            <h3>Добавить вопрос</h3>
            <div class="s_tests_add_form">
                <input type="text" placeholder="Введите название вопроса" class="s_tests_add_question" />
                <span><i class="fa fa-plus s_tests_add_question_com" title="Добавить вопрос"></i></span>
            </div>
            <div class="s_tests_add_res">
            
            </div>
        </div>
    </div>
    <div class="s_struktura_s_tests_div_options">
        <div>
            <h3>Опции теста</h3>
            <div class="s_tests_div_options_item s_tests_cnt_quest"></div>
    
            
            <div class="s_tests_div_options_item">
                <p><label><input type="checkbox" class="s_test_chk_reg" name="s_test_chk_reg"/> Отображать зарегистрированным пользователям</label></p>
            </div>
            <div class="s_tests_div_options_item">
                <p><label><input type="checkbox" class="s_test_chk_rand_quest" name="s_test_chk_rand_quest"/> Перемешать вопросы</label></p>
            </div>
            <div class="s_tests_div_options_item">
                <p><label><input type="checkbox" class="s_test_chk_rand_answer" name="s_test_chk_rand_answer"/> Перемешать ответы</label></p>
            </div>
            <hr />
            <div class="s_tests_div_options_item">
                <p>Дата начала теста</p>
                <input type="text" class="s_test_data_start" name="s_test_data_start" placeholder="Дата начала теста" />
            </div>
            <div class="s_tests_div_options_item">
                <p>Дата окончания теста</p>
                <input type="text" class="s_test_data_end" name="s_test_data_end" placeholder="Дата окончания теста"  />
            </div>
            
            
            <div class="s_tests_div_options_item">
                <p>Количество попыток</p>
                <input type="text" class="s_test_cnt_try" name="s_test_cnt_try" placeholder="Количество попыток"  />
            </div>
            
            <div class="s_tests_div_options_item">
                <p>Количество вопросов в тесте</p>
                <input type="text" class="s_test_cnt_quest" name="s_test_cnt_quest" placeholder="Количество вопросов в тесте"  />
            </div>
            
            <div class="s_tests_div_options_item">
                <p>Время на прохождение теста</p>
                <input type="text" class="s_test_time_for_test" name="s_test_time_for_test" placeholder="Время на прохождение теста"  />
            </div>
            <div class="s_tests_div_options_item">
                <p>Очистить результаты по данному тесту</p>
                <select class="i_contr_id" data-placeholer="Укажите пользователя"><option value="-1">[ВСЕ]</option></select>
                <span class="btn_gray s_test_clear_all_results">Очистить</span>
            </div>
        </div>
    </div>
    
</div>
</div>