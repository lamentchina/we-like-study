<?php
include_once dirname(__FILE__).'/../../model/question.php';
include_once dirname(__FILE__).'/../../model/quiz.php';
include_once dirname(__FILE__).'/../../model/quiz/paper.php';

class install_yf extends wls {

	public $types = null;
	public $id_quiz = 0;
	public $id_quiz_paper = 0;
	public $quizData = null;

	public $ids = null;
	public $paperHtmlContent = null;
	public $allIds = null;

	public $questions = array();
	public $checkLength = 0;
	public $choiceLength = 10;
	public $multiChoiceLength = 30;
	public $blankLength = 0;

	public $order = array('choice');
	public $role = 1;

	public $id_level_subject = '520103';
	function install_yf(){
		session_start();
		if(isset($_SESSION['id_level_subject'])){
			$this->id_level_subject = $_SESSION['id_level_subject'];
		}
		parent::wls();
		$this->types = array(
			'100201'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f二级f/fACCESS笔试f" 
		),
			'100202'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f二级f/fC++笔试f" 
		),
			'100203'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f二级f/fC语言笔试f" 
		),
			'100204'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f二级f/fJAVA笔试f" 
		),
			'100205'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f二级f/fVISUAL BASIC笔试f" 
		),
			'100206'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f二级f/fVISUAL FOXPRO笔试f" 
		),
			'100301'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f三级f/fPC技术笔试f" 
		),
			'100302'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f三级f/f数据库技术笔试f" 
		),
			'100303'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f三级f/f网络技术笔试f" 
		),
			'100304'=> array(
				'su_id'=>3
		,'mainfolder'=>'f计算机f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f计算机f/f模拟题f/f计算机等级考试国家)f/f三级f/f信息管理技术笔试f" 
		),
			'3002'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f大学英语f/f大学英语四级f" 
		),
			'3003'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f大学英语f/f大学英语六级f" 
		),
			'3004'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f大学英语f/f专升本英语f" 
		),
			'3001'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f大学英语f/f大学英语三级A)f" 
		),
			'3001'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f大学英语f/f大学英语三级B)f" 
		),
			'3102'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f公共英语f/f公共英语二级f" 
		),
			'3103'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f公共英语f/f公共英语三级f" 
		),
			'3104'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f公共英语f/f公共英语四级f" 
		),
			'3105'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f公共英语f/f公共英语五级f" 
		),
			'3101'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		//				,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f公共英语f/f公共英语一级B)f"
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f公共英语f/f公共英语一级f"  
		),
			'3202'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f专业英语f/f专业英语八级f" 
		),
			'3201'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f专业英语f/f专业英语四级f" 
		),
			'3301'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f资格英语f/fGREf" 
		),
			'3303'=> array(
				'su_id'=>2
		,'mainfolder'=>'f语言f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f资格英语f/f雅思f" 
		),

			'510201'=> array(
				'su_id'=>4
		,'mainfolder'=>'f法律f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f真题f/f司法f/f卷一f" 
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f模拟题f/f司法f/f卷一f" 
		),	
			'510202'=> array(
				'su_id'=>4
		,'mainfolder'=>'f法律f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f真题f/f司法f/f卷二f"
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f模拟题f/f司法f/f卷二f"  
		),		
			'510203'=> array(
				'su_id'=>4
		,'mainfolder'=>'f法律f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f真题f/f司法f/f卷三f"
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f模拟题f/f司法f/f卷三f"  
		),	
			'510204'=> array(
				'su_id'=>4
		,'mainfolder'=>'f法律f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f真题f/f司法f/f卷四f" 
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f模拟题f/f司法f/f卷四f" 
		),	
			'510101'=> array(
				'su_id'=>4
		,'mainfolder'=>'f法律f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f真题f/f司法f/f卷四f" 
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f模拟题f/f企业法律顾问f/f民商与经济法律知识f" 
		),	
			'510102'=> array(
				'su_id'=>4
		,'mainfolder'=>'f法律f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f真题f/f司法f/f卷四f" 
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f模拟题f/f企业法律顾问f/f企业管理知识f" 
		),	
			'510103'=> array(
				'su_id'=>4
		,'mainfolder'=>'f法律f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f真题f/f司法f/f卷四f" 
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f模拟题f/f企业法律顾问f/f综合法律知识f" 
		),	
			'510104'=> array(
				'su_id'=>4
		,'mainfolder'=>'f法律f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f真题f/f司法f/f卷四f" 
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f法律f/f模拟题f/f企业法律顾问f/f企业法律顾问实务f" 
		),					
			'9001'=> array(
				'su_id'=>4
		,'mainfolder'=>'f法律f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f语言f/f模拟题f/f剑桥商务英语f/f中级f" 
		),
			'8001'=> array(
				'su_id'=>5
		,'mainfolder'=>'f公务员f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f公务员f/f模拟题f/f国家公务员f/f公安基础知识模拟题f" 
		),
			'500101'=> array(
				'su_id'=>5
		,'mainfolder'=>'f公务员f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f公务员f/f模拟题f/f国家公务员f/f行政职业能力测试模拟题f" 
		),
			'500102'=> array(
				'su_id'=>5
		,'mainfolder'=>'f公务员f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f公务员f/f模拟题f/f国家公务员f/f公共基础知识模拟题f" 
		),
			'500103'=> array(
				'su_id'=>5
		,'mainfolder'=>'f公务员f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f公务员f/f模拟题f/f国家公务员f/f公安基础知识模拟题f" 
		),
			'50020302'=> array(
				'su_id'=>5
		,'mainfolder'=>'f公务员f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f公务员f/f模拟题f/f地方公务员f/f浙江f/f行政职业能力测验f" 
		),	
			'500104'=> array(
				'su_id'=>5
		,'mainfolder'=>'f公务员f'
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f公务员f/f模拟题f/f国家公务员f/f申论模拟题f" 
		),	
			'4001'=> array(
				'su_id'=>14
		,'mainfolder'=>'f医学f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学Af/f模拟题f/f医师考试f/f公卫执业医师f"
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学Af/f模拟题f/f医师考试f/f公卫执业助理医师f"
		),
			'4003'=> array(
				'su_id'=>14
		,'mainfolder'=>'f医学f'
		//,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学Af/f模拟题f/f医师考试f/f临床执业医师f"
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学Af/f模拟题f/f医师考试f/f临床执业助理医师f"
		//,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学f/f分类模拟题f/f执业医师f/f临床执业医师f/f综合模拟题f" 
		),	
			'4004'=> array(
				'su_id'=>14
		,'mainfolder'=>'f医学f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学Af/f模拟题f/f医师考试f/f中医执业医师f"
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学Af/f模拟题f/f医师考试f/f中医执业助理医师f"
		
		),	
			'4005'=> array(
				'su_id'=>14
		,'mainfolder'=>'f医学f'
//		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学Af/f模拟题f/f医师考试f/f中西医结合执业医师f"
		,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f医学Af/f模拟题f/f医师考试f/f中西医结合执业助理医师f"
		
		),	
		'520101'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f会计从业资格f/f国家f/f财经法规与会计职业道德f"
		),	
		'520102'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f会计从业资格f/f国家f/f初级会计电算化f"
		),	
		'520103'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f会计从业资格f/f国家f/f会计基础f"
		),	
		'520201'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f初级会计f/f会计实务模拟题f"
		),	
		'520202'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f初级会计f/f经济法基础模拟题f"
		),	
		'520301'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f中级会计f/f财务管理模拟题f"
		),	
		'520302'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f中级会计f/f会计实务模拟题f"
		),	
		'520303'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f中级会计f/f经济法模拟题f"
		),	
		'520401'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f高级会计师f/f高级会计实务f"
		),	
		'520501'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f注册会计师f/f财务成本管理模拟题f"
		),		
		'520502'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f注册会计师f/f公司战略与风险管理模拟题f"
		),	
		'520503'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f注册会计师f/f会计模拟题f"
		),	
		'520504'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f注册会计师f/f经济法模拟题f"
		),		
		'520505'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f注册会计师f/f审计模拟题f"
		),	
		'520506'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f注册会计师f/f税法模拟题f"
		),		
		'5301'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f银行业从业人员资格考试f/f风险管理f"
		),	
		'5302'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f银行业从业人员资格考试f/f个人贷款f"
		),	
		'5303'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f银行业从业人员资格考试f/f个人理财f"
		),	
		'5304'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f银行业从业人员资格考试f/f公司信贷f"
		),
		'5305'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f银行业从业人员资格考试f/f公共基础f"
		),		
		'540101'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f证券业从业资格考试f/f证券发行与承销f"
		),
		'540102'=> array(
			'su_id'=>6
			,'mainfolder'=>'f经济f'
			,'path'=>"E:/Projects/WEBS/PHP/phpwind_UTF8_8_3/upload/apps/wls/app/free/controller/install/f经济f/f模拟题f/f证券业从业资格考试f/f证券交易f"
		),
		);
	}

	public function downLoadAll(){
		header("Content-type: text/html; charset=utf-8");
		$url = "http://www.yfzxmn.cn/com/left/left.jsp?so_id=".$_REQUEST['so']."&su_id=".$_REQUEST['su'];
		$folder = "";
		if(isset($_REQUEST['folder'])){
			$folder = str_replace("_","/",$_REQUEST['folder'])."/";
		}

		$content = file($url);
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("\n","",$content);

		$menuStr = $content;
		$menuStr = str_replace("<img src=/icon/CDefault.gif width=20 >","",$menuStr);
		$menuStr = str_replace("<img src=/icon/CFolder.gif width=20 >","",$menuStr);
		$menuStr = str_replace("<font style=\"cursor:hand;\" onClick=","",$menuStr);
		$menuStr = str_replace("(","",$menuStr);
		$menuStr = str_replace("'","",$menuStr);
		$menuStr = str_replace("this)>","",$menuStr);
		$arr = explode("loadid",$menuStr);
		if($arr==false || count($arr)<2){
			$data = array(
				'folderLeaf'=>1
			);
			echo json_encode($data);

			$arr2 = explode("ex_id=",$content);
			$ids = '';
			for($i=1;$i<count($arr2);$i++){
				$arr3 = explode("&ef_id",$arr2[$i]);
				$ids .= $arr3[0].",";
			}
			$ids = substr($ids,0,strlen($ids)-1);
			$path = dirname(__FILE__);
			$fileName = $path.$folder."ids.txt";
			$fileName = mb_convert_encoding($fileName,'GBK','UTF-8');
			if(file_exists($fileName)){

			}else{
				$handle=fopen($fileName,"a");
				fwrite($handle,$ids);
				fclose($handle);
			}
			exit();
		}

		$data = array();
		for($i=1;$i<count($arr);$i++){
			$arr2 = explode(",",$arr[$i]);
			$arr3 = explode("</font>",$arr2[3]);
			$item = array(
				 'su'=>$arr2[0]
			,'so'=>$arr2[1]
			,'name'=>"f".$arr3[0]."f"
			,'folder'=>$folder."f".$arr3[0]."f_"
			);
			$data[] = $item;
			$path = dirname(__FILE__);
			$path = $path.$folder."f".$arr3[0]."f";
			//echo $path;
			$path = mb_convert_encoding($path,'GBK','UTF-8');
			mkdir($path,0777);
		}
		$data = array(
			'data'=>$data,
			'folderLeaf'=>0
		);
		echo json_encode($data);
	}

	public function downLoadAllView(){
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"../libs/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"../libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var downLoadAll = function(so,su,folder){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install_yf&action=downLoadAll',
		data: {so:so,su:su,folder:folder},
		success: function(msg){
			var obj = jQuery.parseJSON(msg);
			if(obj.folderLeaf==1){
				
			}else{
				for(var i=0;i<obj.data.length;i++){
					downLoadAll(obj.data[i].so,obj.data[i].su,obj.data[i].folder);
				}
			}
		}
	});
}
downLoadAll(0,".$this->types[$this->id_level_subject]['su_id'].",'_".$this->types[$this->id_level_subject]['mainfolder']."');
</script>
</head>
<body>
<div id='index'><div>
<div id=\"console\"><div>
</body>
</html>
		";
		echo $html;
	}

	function html(){
		if(isset($_REQUEST['id_level_subject'])){
			
//			session_start();
			$_SESSION['id_level_subject'] = $_REQUEST['id_level_subject'];
			$this->id_level_subject = $_REQUEST['id_level_subject'];
		}
		$filename = $this->types[$this->id_level_subject]['path']."/ids.txt";
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		$content = file( $filename );
		$content = implode("\n", $content);
		$content = str_replace("\n","",$content);
		$html = "
<html>
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<script src=\"../libs/jquery-1.4.2.js\" type=\"text/javascript\"></script>
<script src=\"../libs/jqueryextend.js\" type=\"text/javascript\"></script>
<script type=\"text/javascript\">
var ids = [".$content."];
var index = 0;
var down = function(){
	$.ajax({
		type: 'post',
		url: 'wls.php?controller=install_yf&action=down',
		data: {id:ids[index]},
		success: function(msg){
			if(index==ids.length )return;
			down();			
			index++;
		}
	});
}
down();
</script>
</head>
<body>
<div id='index'><div>
<div id=\"console\"><div>
</body>
</html>
		";
		echo $html;
	}
	


	function down(){
		$filename = $this->types[$this->id_level_subject]['path']."/".$_REQUEST['id'].".html";
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');

		if(!file_exists($filename)){
			$content = file("http://www.yfzxmn.cn/user/exam/examcontext.jsp?su_id=".$this->types[$this->id_level_subject]['su_id']."&ex_id=".$_REQUEST['id']);
			$content = implode("\n", $content);
			$handle=fopen($filename,"a");
			fwrite($handle,$content);
			fclose($handle);

			//			$this->readFile();
		}else{
//						$this->readFile();
		}
		echo mb_convert_encoding($filename,'UTF-8','GBK');
	}

	public function getPaper(){
		$pfx = $this->c->dbprefix;
		$conn = $this->conn();
		$sql = "select * from ".$pfx."wls_subject where id_level = '".$this->id_level_subject."';";
		$res = mysql_query($sql,$conn);
		$temp = mysql_fetch_assoc($res);

		$data = array(
			  'id_level_subject'=>$this->id_level_subject
		,'name_subject'=>$temp['name']
		,'title'=>$temp['name'].'_'.rand(1,10000)
		,'author'=>'admin'
		);
		$this->quizData = $data;

		$quizObj = new m_quiz();
		$this->id_quiz = $quizObj->insert($data);

		$paperObj = new m_quiz_paper();
		$data = array(
			 'id_quiz'=>$this->id_quiz
		,'money'=>rand(0,10)
		);
		$this->id_quiz_paper = $paperObj->insert($data);
	}


	public function getCheckQuestions(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,"判断题");		
		$p2 = strpos($content,"计算题");		
		$content = substr($content,$p1,$p2-$p1);
		//		$checkPos = strpos($content,"s39tan");
		//		if($checkPos!=false){
		//			 $this->checkLength = 40;
		//		}
		$len = $this->choiceLength + $this->multiChoiceLength;

		for($i=1;$i<=$this->checkLength;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"name=s".($i+$len-1)."fs");
			$data = substr($content,$p1,$p2-$p1);

			$p1 = strpos($data,"<a name='anway'>");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".</td><td width='99%'>","",$title);
			$title = str_replace("</table>","",$title);
			$title = str_replace("</td>","",$title);
			$title = str_replace("</tr>","",$title);
			$title = $this->t->formatTitle($title);

			$p1 = strpos($data,"name=s".($i+$len-1)."an");
			$p2 = strpos($data,"name=s".($i+$len-1)."t");
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("name=s".($i+$len-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);
			$description = str_replace("<br>","",$description);

			$this->questions[] = array(
				 'title'=>$title
			,'answer'=>$answer
			,'description'=>$description
			,'date_created'=>date('Y-m-d H:i:s')
			,'markingmethod'=>'自动批改'
			,'type'=>'判断题'
			,'belongto'=>0
			,'index' =>"1".$i
			,'cent'=>1

			,'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']

			,'ids_level_knowledge'=>$this->knowledges[rand(0,24)]
			);
		}
	}

	public function getChoiceQuestions(){
		echo 2341234;
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,"选择题");
		$p2 = strpos($content,"填空题");	

		if($p1==false){
			$p1 = 0;
			$p2 = strlen($content);
		}
		$content = substr($content,$p1,$p2-$p1);

		$arr = explode("fs type=hidden",$content);
		$len =  count($arr)-2;
		$this->choiceLength = $len;

		$len = count($this->questions);
		for($i=1;$i<=$this->choiceLength;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"name=s".($i+$len-2)."fs");
			$data = substr($content,$p1,$p2-$p1);
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);

			$p1 = strpos($data,"A.");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = $this->t->formatTitle($title);
			$title = str_replace("<br/>&nbsp;&nbsp;<br/>&nbsp;&nbsp;","<br/>",$title);

			$p1 = strpos($data,"A.");
			$p2 = strpos($data,"B.");
			$A = substr($data,$p1,$p2-$p1);
			$A = trim(str_replace("A.","",$A));

			$p1 = strpos($data,"B.");
			$p2 = strpos($data,"C.");
			$B = substr($data,$p1,$p2-$p1);
			$B = trim(str_replace("B.","",$B));

			$p1 = strpos($data,"C.");
			$p2 = strpos($data,"D.");
			$C = substr($data,$p1,$p2-$p1);
			$C = trim(str_replace("C.","",$C));

			$p1 = strpos($data,"D.");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D.","",$D);
			$D = trim(str_replace(">".$i,"",$D));
				
			$p1 = strpos($data,"E.");
			if($p1!=false){
				$p2 = strpos($data,"<",$p1);
				$E = substr($data,$p1,$p2-$p1);
				$E = str_replace("E.","",$E);
				$E = trim(str_replace(">".$i,"",$E));
			}
				
			//			echo $data;exit();
			$p1 = strpos($data,"name=s".($i+$len-2)."an");
			$p2 = strpos($data,"name=s".($i+$len-2)."t");
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("name=s".($i+$len-2)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
			//			echo $answer;exit();

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
			//			echo $description;exit();
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);
			$description = str_replace("<br>","",$description);

			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>2
			,'option1'=>$this->t->formatTitle($A)
			,'option2'=>$this->t->formatTitle($B)
			,'option3'=>$this->t->formatTitle($C)
			,'option4'=>$this->t->formatTitle($D)
			,'optionlength'=>4
			,'type'=>'单项选择题'
			,'answer'=>$answer
			,'description'=>$description
			,'index' =>"10".( ($i>9)?$i:('0'.$i) )
			);
				
			if(isset($E)){
				$question['option5'] = $E;
				$question['optionlength'] = 5;
			}
			//			print_r($question);exit();
			$this->questions[$question['index']] = $question;
		}
	}

	public function getMultiChoiceQuestions(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,"多项选择题");
		$p2 = strpos($content,"判断题");	
		$content = substr($content,$p1,$p2-$p1);
		//		$checkPos = strpos($content,"s104tan");
		//		if($checkPos!=false){
		//			 $this->multiChoiceLength = 30;
		//		}
		$len =  $this->choiceLength;

		for($i=1;$i<=$this->multiChoiceLength;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"name=s".($i+$len-1)."fs");
			$data = substr($content,$p1,$p2-$p1);
			$data = str_replace("<td width=\"50%\">","",$data);
			$data = str_replace("</td>","",$data);
			$data = str_replace("<tr>","",$data);
			$data = str_replace("</tr>","",$data);
			$data = str_replace("</div>","",$data);
			$data = str_replace("<div>","",$data);

			$p1 = strpos($data,"A．");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = $this->t->formatTitle($title);

			$p1 = strpos($data,"A．");
			$p2 = strpos($data,"B．");
			$A = substr($data,$p1,$p2-$p1);
			$A = trim(str_replace("A．","",$A));

			$p1 = strpos($data,"B．");
			$p2 = strpos($data,"C．");
			$B = substr($data,$p1,$p2-$p1);
			$B = trim(str_replace("B．","",$B));

			$p1 = strpos($data,"C．");
			$p2 = strpos($data,"D．");
			$C = substr($data,$p1,$p2-$p1);
			$C = trim(str_replace("C．","",$C)); 

			$p1 = strpos($data,"D．");
			$p2 = strpos($data,"<",$p1);
			$D = substr($data,$p1,$p2-$p1);
			$D = str_replace("D．","",$D);
			$D = trim(str_replace(">".$i,"",$D));

			$p1 = strpos($data,"name=s".($i+$len-1)."an");
			$p2 = strpos($data,"name=s".($i+$len-1)."t");
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("name=s".($i+$len-1)."an type=hidden value=\"","",$answer);
			$answer = str_replace("\"><input","",$answer);
			$answer = trim($answer);
			$answerArr = array();
			for($i2=0;$i2<strlen($answer);$i2++){
				$answerArr[] = substr($answer,$i2,1);
			}
			$answer = implode(',',$answerArr);

			$p1 = strpos($data,"amwser.gif");
			$description = substr($data,$p1);
			$description = str_replace("amwser.gif>","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input","",$description);
			$description = str_replace("<br>","",$description);

			$question = array(
				 'id_level_subject'=>$this->paper['id_level_subject']
			,'name_subject'=>$this->paper['name_subject']
			,'id_quiz_paper'=>$this->paper['id']
			,'title_quiz_paper'=>$this->paper['title']
			,'title'=>$title
			,'cent'=>4
			,'option1'=>$A
			,'option2'=>$B
			,'option3'=>$C
			,'option4'=>$D
			,'optionlength'=>4
			,'date_created'=>date('Y-m-d H:i:s')
			,'markingmethod'=>'自动批改'
			,'type'=>'多项选择题'
			,'answer'=>$answer
			,'description'=>$description

			,'belongto'=>0
			,'index' =>"3".$i

			,'ids_level_knowledge'=>$this->knowledges[rand(0,24)]
			);

			$this->questions[] = $question;
		}
	}

	public function getBlankQuestions(){
		$content = $this->paperHtmlContent;
		$p1 = strpos($content,"填空题");
		$p2 = strlen($content);
		$content = substr($content,$p1,$p2-$p1);

		$arr = explode("fs type=hidden",$content);
		$len = count($arr)-2;
		$this->blankLength = $len;

		$len = count($this->questions);

		for($i=1;$i<=$this->blankLength;$i++){
			$p1 = strpos($content,">".$i.".</td>");
			$p2 = strpos($content,"s".($i+$len-3)."fs");
			$data = substr($content,$p1,$p2-$p1);
			//			echo $data;
			//			continue;

			$data = str_replace("<td width='99%'>","",$data);
			$data = str_replace(">".$i.".","",$data);
			$data = str_replace("</td></tr></table>","",$data);

			$p1 = strpos($data,"<a name='anway'>");
			$title = substr($data,0,$p1);
			$title = str_replace(">".$i.".<td width='99%'>","",$title);
			$title = str_replace("<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">","",$title);
			$title = str_replace("<u>","",$title);
			$title = str_replace("</u>","",$title);
			$title = str_replace("</td>","",$title);
			$title = str_replace("______","[___1___]",$title);
			$title = str_replace("_____","[___1___]",$title);
			for($i2=1;$i2<30;$i2++){
				$title = str_replace("【".$i2."】","[___1___]",$title);
			}
			for($i2=1;$i2<30;$i2++){
				$title = str_replace("[".$i2."]","[___1___]",$title);
			}
			$title = $this->t->formatTitle($title);

			$p1 = strpos($data,"<IMG src=image/amwser.gif>");
			$p2 = strpos($data,"<br>",$p1);
			$answer = substr($data,$p1,$p2-$p1);
			$answer = str_replace("<IMG src=image/amwser.gif>","",$answer);
			$answer = trim($answer);


			$p1 = strpos($data,"<br>[知识点]");
			$description = substr($data,$p1);
			$description = str_replace("<br>[知识点]","",$description);
			$description = str_replace("</TD></TR></TBODY></TABLE><br><input name=","",$description);


			$question = array(
				 'id_quiz'=>$this->id_quiz
			,'title'=>$title
			,'cent'=>0
			,'type'=>'填空题'
			,'index' =>"20".( ($i>9)?$i:('0'.$i) )
			);
			$this->questions[$question['index']] = $question;


			$question2 = array(
				 'id_quiz'=>$this->id_quiz
			,'cent'=>2
			,'type'=>'填空题'
			,'title'=>1
			,'belongto'=>$question['index']
			,'answer'=>$answer
			//,'description'=>$description
			,'index' =>"20".( ($i>9)?$i:('0'.$i) )."01"
			);
			$this->questions[$question2['index']] = $question2;
		}
		//		print_r($this->questions);
		//		exit();
	}

	public function getQuestions(){
		for($i=0;$i<count($this->order);$i++){
			if($this->order[$i]=='choice'){
				//				continue;
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'title'=>'单项选择题'
				,'type'=>'组合题'
				,'index'=>10
				);
				$this->questions[$question['index']] = $question;
				$this->getChoiceQuestions();
			}else if($this->order[$i]=='blank'){
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'title'=>'填空题'
				,'type'=>'组合题'
				,'index'=>20
				);
				$this->questions[$question['index']] = $question;
				$this->getBlankQuestions();
			}else if($this->order[$i]=='multi'){
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'title'=>'多项选择题'
				,'type'=>'组合题'
				,'index'=>30
				);
				$this->questions[$question['index']] = $question;
				$this->getMultiChoiceQuestions();
			}else if($this->order[$i]=='check'){
				$question = array(
					 'id_quiz'=>$this->id_quiz
				,'title'=>'判断题'
				,'type'=>'组合题'
				,'index'=>40
				);
				$this->questions[$question['index']] = $question;
				$this->getCheckQuestions();
			}
		}
	}

	public function readFile(){
		$filename = $this->types[$this->id_level_subject]['path'].'/'.$_REQUEST['id'].'.xls';
		$filename = mb_convert_encoding($filename,'GBK','UTF-8');
		if(file_exists($filename)){
//			echo mb_convert_encoding($filename,'GBK','UTF-8');
			echo mb_convert_encoding($filename,'UTF-8','GBK');
			return;
		}

		$path = $this->types[$this->id_level_subject]['path']."/".$_REQUEST['id'].".html";
		$path = mb_convert_encoding($path,'GBK','UTF-8');
		//		echo $path;exit();
		$content = file($path);
		$content = implode("\n", $content);
		$content = mb_convert_encoding($content,'UTF-8','GBK');
		$content = str_replace("DISPLAY: none","",$content);
		$content = str_replace("A)","A.",$content);
		$content = str_replace("B)","B.",$content);
		$content = str_replace("C)","C.",$content);
		$content = str_replace("D)","D.",$content);
		$content = str_replace("E)","E.",$content);
		$content = str_replace("F)","F.",$content);

		$content = str_replace("A．","A.",$content);
		$content = str_replace("B．","B.",$content);
		$content = str_replace("C．","C.",$content);
		$content = str_replace("D．","D.",$content);	
		$content = str_replace("E．","E.",$content);
		$content = str_replace("F．","F.",$content);	
		$content = str_replace("<a id=\"donw\" href=\"","",$content);
		header("Content-type: text/html; charset=utf-8");
		//		echo $content;
		//		exit();
		$this->paperHtmlContent = $content;
		$this->getPaper();
		$this->getQuestions();
		//		print_r($this->questions);
		//		exit();
		$questionObj = new m_question();
		$this->questions = $questionObj->insertMany($this->questions);
		$this->questions = array_values($this->questions);
		$ids = '';
		for($i=0;$i<count($this->questions);$i++){
			$ids .= $this->questions[$i]['id'].',';
		}
		$ids = substr($ids,0,strlen($ids)-1);
		$quizObj = new m_quiz();
		$quizObj->update(array(
			 'id'=>$this->id_quiz
		,'ids_questions'=>$ids
		));

		$paprObj = new m_quiz_paper();
		$paprObj->id_paper = $this->id_quiz_paper;

		$paprObj->exportOne($filename);
	}
}
?>