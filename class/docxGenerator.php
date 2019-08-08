<?php 
/*-->docxGenerator.php<--*/ 
//Класс, генерирующий docx-файлы на базе шаблонов 
$name_f='document';
class docxGenerator {
 private $_zipObject; //Для открытия zip-архива 
 private $_tmpFilename; //Имя временного файла, создаваемого при работе класса 
 private $_docxContent; //Хранит содержимое ./word/document.xml 
 private $_docxFooter; //Хранит содержимое ./word/footer1.xml
 private $_docxHeader; //Хранит содержимое ./word/footer1.xml 
 private $_docxFooter2; //Хранит содержимое ./word/footer1.xml
 private $_docxHeader2; //Хранит содержимое ./word/footer1.xml 
 private $_docxFooter3; //Хранит содержимое ./word/footer1.xml
 private $_docxHeader3; //Хранит содержимое ./word/footer1.xml 
 private $_docxdocProps1;
 private $_docxdocProps2;
 private $_docxRels; //Хранит содержимое ./word/footer1.xml 
 public function __construct($filename){ 
  //Конструтор класса, берет шаблон $filename 

  //1) Создаем копию шаблона для безопасной работы 
  $this->_tmpFilename = dirname($filename).time().'.docx'; // Функция dirname извлекает путь к каталогу с файлом filename 
  copy($filename, $this->_tmpFilename); // Копируем содержимое шаблона во временный файл 

  //2) С помощью встроенного в PHP класса вытаскиваем содержимое 
  $this->_zipObject = new ZipArchive(); //Создали экземпляр класса для работы с Zip-архивом 
  $this->_zipObject->open($this->_tmpFilename); //Открыли временный файл архиватором, т.к. docx - это и есть архив 
  $this->_docxContent = $this->_zipObject->getFromName('word/document.xml'); //Вытащили текст документа с разметкой из файла ./word/document.xml внутри архива 
  $this->_docxFooter = $this->_zipObject->getFromName('word/footer1.xml'); //Вытащили текст документа с разметкой из файла ./word/footer1.xml внутри архива 
  $this->_docxHeader = $this->_zipObject->getFromName('word/header1.xml'); //Вытащили текст документа с разметкой из файла ./word/header1.xml внутри архива 
  $this->_docxFooter2 = $this->_zipObject->getFromName('word/footer2.xml'); //Вытащили текст документа с разметкой из файла ./word/footer1.xml внутри архива 
  $this->_docxHeader2 = $this->_zipObject->getFromName('word/header2.xml'); //Вытащили текст документа с разметкой из файла ./word/header1.xml внутри архива 
  $this->_docxFooter3 = $this->_zipObject->getFromName('word/footer3.xml'); //Вытащили текст документа с разметкой из файла ./word/footer1.xml внутри архива 
  $this->_docxHeader3 = $this->_zipObject->getFromName('word/header3.xml'); //Вытащили текст документа с разметкой из файла ./word/header1.xml внутри архива 
  $this->_docxdocProps1 = $this->_zipObject->getFromName('docProps/app.xml'); //Вытащили текст документа с разметкой из файла ./word/header1.xml внутри архива 
  $this->_docxdocProps2 = $this->_zipObject->getFromName('docProps/core.xml'); //Вытащили текст документа с разметкой из файла ./word/header1.xml внутри архива 
  
  
  $this->_docxRels = $this->_zipObject->getFromName('word/_rels/document.xml.rels'); //Вытащили текст документа с разметкой из файла ./word/document.xml внутри архива 
 }//__construct 

 public function val($search, $replace) { 
  //Функция замены меток с названием $search на значение $replace 
  //$search = '@@'.$search.'@@'; //Прибавляем амперсанд в виде специального символа и точку с запятой 
  $this->_docxContent = str_ireplace($search, $replace, $this->_docxContent); //Собственно процесс замены это обычная замена подстрок в текстовом документе 
  $this->_docxFooter = str_ireplace($search, $replace, $this->_docxFooter); //Собственно процесс замены это обычная замена подстрок в текстовом документе 
  $this->_docxHeader = str_ireplace($search, $replace, $this->_docxHeader); //Собственно процесс замены это обычная замена подстрок в текстовом документе   
  
  $this->_docxFooter2 = str_ireplace($search, $replace, $this->_docxFooter2); //Собственно процесс замены это обычная замена подстрок в текстовом документе 
  $this->_docxHeader2 = str_ireplace($search, $replace, $this->_docxHeader2); //Собственно процесс замены это обычная замена подстрок в текстовом документе   
  $this->_docxFooter3 = str_ireplace($search, $replace, $this->_docxFooter3); //Собственно процесс замены это обычная замена подстрок в текстовом документе 
  $this->_docxHeader3 = str_ireplace($search, $replace, $this->_docxHeader3); //Собственно процесс замены это обычная замена подстрок в текстовом документе   
    
  $this->_docxdocProps1 = str_ireplace($search, $replace, $this->_docxdocProps1); //Собственно процесс замены это обычная замена подстрок в текстовом документе   
  $this->_docxdocProps2 = str_ireplace($search, $replace, $this->_docxdocProps2); //Собственно процесс замены это обычная замена подстрок в текстовом документе   
  
  
  $this->_docxRels = str_ireplace($search, $replace, $this->_docxRels); //Собственно процесс замены это обычная замена подстрок в текстовом документе  
 }//val 

 public function save($filename){
  //Сохраняет полученный из шаблона файл с именем $filename. Существующие файлы перезаписываются. 

  //1) Если файл $filename уже существует, то нужно его удалить 
  if(file_exists($filename)){ 
   unlink($filename); 
  }//if file_exists 

  //2) Дописываем измененное xml-содержимое в документ 
  $this->_zipObject->addFromString('word/document.xml', $this->_docxContent); 
	$this->_zipObject->addFromString('word/footer1.xml', $this->_docxFooter); 
	$this->_zipObject->addFromString('word/header1.xml', $this->_docxHeader);
	$this->_zipObject->addFromString('word/footer2.xml', $this->_docxFooter2); 
	$this->_zipObject->addFromString('word/header2.xml', $this->_docxHeader2);
	$this->_zipObject->addFromString('word/footer3.xml', $this->_docxFooter3); 
	$this->_zipObject->addFromString('word/header3.xml', $this->_docxHeader3);
	$this->_zipObject->addFromString('docProps/app.xml', $this->_docxdocProps1); 
	$this->_zipObject->addFromString('docProps/core.xml', $this->_docxdocProps2);


	$this->_zipObject->addFromString('word/_rels/document.xml.rels', $this->_docxRels); 	
  //3) Пытаемся сохранить изменения 
  if($this->_zipObject->close() === false){ 
   throw new Exception('Не удалось сохранить изменения в документе.'); 
  }//if close 
  rename($this->_tmpFilename, $filename); 
 }//save 
}//docxGenerator 
?>