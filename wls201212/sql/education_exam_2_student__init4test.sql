CREATE PROCEDURE `education_exam_2_student__init4test`()
pro_main:BEGIN
/*
模拟统考数据,整一年的数据
5个班级,201个学生,高三一年,8个月,每个月一次月考
2011年秋季到2012年春季,每个学生的做题情况,他们的成绩按月份逐月上升

月份 概率 浮动
9    60%  10%
10   70%  9%
11   80%  8% 
12   85%  7%
3    87%  6%
4    88%  5%
5    90%  4%
6    95%  3%

按月份循环 * 8
  插入一份统考试卷
  插入一张试卷  
  插入50道题目  
    插入30道单选题    
    插入10道多选题    
    插入10道单选题    
  循环5个班级,插入统考安排信息
    插入1条 考试-班级 信息    
    按每个学生循环 * 40   
      插入1条 学生-考试 信息    
      插入1条试卷做题日志    
      插入50条做题日志
        根据月份,设置每一题答对概率    
      卷子提交        
        更新卷子日志的成绩      
        更新 学生-考试 信息        
    更新班级的统考统计成绩: 平均分,最高分,及格人数

*/

#科目信息
declare subject_code_ char(2) default '00';
declare subject_name_ char(4) default '0000';

#学生信息
declare student_id_,student_group_id_ int default '0';
declare student_group_code_,student_group_name_ varchar(200) default '0';




END;
