<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<script src="libs/DWZ/javascripts/jquery-1.4.2.js" type="text/javascript"></script>
<script src="libs/jqueryextend.js" type="text/javascript"></script>
<script src="view/wls.js" type="text/javascript"></script>
<script src="view/quiz/quiz.js" type="text/javascript"></script>
<script src="view/quiz/paper.js" type="text/javascript"></script>
<script src="view/question/question.js" type="text/javascript"></script>
<script src="view/question/choice.js" type="text/javascript"></script>
<script src="view/question/reading.js" type="text/javascript"></script>
<script src="view/question/blank.js" type="text/javascript"></script>
<link href="view/wls.css" rel="stylesheet" type="text/css" />

</head>
<body style="border: 0px; padding: 0px; margin: 0px;">
<div id='wls'><div>
<script type="text/javascript">
var obj_paper = new wls_quiz_paper();

obj_paper.naming ='obj_paper';
obj_paper.quizDomId ='wls';
obj_paper.initLayout();
obj_paper.paperId = <?php echo $id?>;
obj_paper.submitQuesWay = 'onceAll';
var funstr = 'obj_paper.AJAXAllQues("obj_paper.initButton();obj_paper.addSubQuesNav();obj_paper.getClock();")';
//var funstr = 'obj_paper.AJAXAllQues(null)';
obj_paper.AJAXData(funstr);
</script>
</body>
</html>