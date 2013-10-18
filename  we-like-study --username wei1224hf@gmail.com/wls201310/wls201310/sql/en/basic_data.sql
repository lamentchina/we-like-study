insert into basic_parameter (code,value,reference) values ('10','System','basic_group__type');
insert into basic_parameter (code,value,reference) values ('20','Department','basic_group__type');
insert into basic_parameter (code,value,reference) values ('30','Post','basic_group__type');
insert into basic_parameter (code,value,reference) values ('10','Normal','basic_group__status');
insert into basic_parameter (code,value,reference) values ('20','Closed','basic_group__status');
insert into basic_parameter (code,value,reference) values ('10','Node','basic_permission__type');
insert into basic_parameter (code,value,reference) values ('20','Page','basic_permission__type');
insert into basic_parameter (code,value,reference) values ('30','Button','basic_permission__type');
insert into basic_parameter (code,value,reference) values ('40','Logic','basic_permission__type');
insert into basic_parameter (code,value,reference) values ('10','Normal','basic_user__status');
insert into basic_parameter (code,value,reference) values ('20','Closed','basic_user__status');
insert into basic_parameter (code,value,reference) values ('10','System','basic_user__type');
insert into basic_parameter (code,value,reference) values ('20','Student','basic_user__type');
insert into basic_parameter (code,value,reference) values ('30','Teacher','basic_user__type');
insert into basic_parameter (code,value,reference) values ('10','Node','exam_subject__type');
insert into basic_parameter (code,value,reference) values ('20','Subject','exam_subject__type');
insert into basic_parameter (code,value,reference) values ('30','Knowledge','exam_subject__type');
insert into basic_parameter (code,value,reference) values ('10','Exam','exam_paper__type');
insert into basic_parameter (code,value,reference) values ('20','Exec','exam_paper__type');
insert into basic_parameter (code,value,reference) values ('10','Normal','exam_paper__status');
insert into basic_parameter (code,value,reference) values ('20','Closed','exam_paper__status');
insert into basic_parameter (code,value,reference) values ('1','Choice','exam_question__type');
insert into basic_parameter (code,value,reference) values ('2','Multichoice','exam_question__type');
insert into basic_parameter (code,value,reference) values ('3','Check','exam_question__type');
insert into basic_parameter (code,value,reference) values ('4','Blank','exam_question__type');
insert into basic_parameter (code,value,reference) values ('5','Mixed','exam_question__type');
insert into basic_parameter (code,value,reference) values ('6','Writing','exam_question__type');
insert into basic_parameter (code,value,reference) values ('7','Title','exam_question__type');
insert into basic_parameter (code,value,reference) values ('1','Horizontal','exam_question__layout');
insert into basic_parameter (code,value,reference) values ('2','vertical','exam_question__layout');
insert into basic_parameter (code,value,reference) values ('10','Normal','exam_paper_log__status');
insert into basic_parameter (code,value,reference) values ('20','Unmark','exam_paper_log__status');



insert into basic_permission (name,type,code,icon,path) values('Login','20','10','../file/icon48x48/login.png','basic_user__login.html'); 
insert into basic_permission (name,type,code,icon,path) values('User Center','20','11','../file/icon48x48/user_center.png','basic_user__center.html'); 
insert into basic_permission (name,type,code,icon,path) values('Modify','30','1101','../file/icon16x16/edit.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Logout','30','1199','../file/icon16x16/logout.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Manger','10','12','../file/icon48x48/manger_center.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Groups','20','1201','../file/icon48x48/user_group.png','basic_group__grid.html'); 
insert into basic_permission (name,type,code,icon,path) values('Add','30','120121','../file/icon16x16/add.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Remove','30','120122','../file/icon16x16/delete.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Modify','30','120123','../file/icon16x16/edit.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Permission','30','120190','../file/icon16x16/config.png',''); 
insert into basic_permission (name,type,code,icon,path) values('User List','20','1202','../file/icon48x48/user_grid.png','basic_user__grid.html'); 
insert into basic_permission (name,type,code,icon,path) values('Search','30','120201','../file/icon16x16/search.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Detail','30','120202','../file/icon16x16/detail.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Groups','30','12020202','../file/icon16x16/detail.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Remove','30','12020222','../file/icon16x16/delete.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Modify','30','12020223','../file/icon16x16/edit.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Permissions','30','12020203','../file/icon16x16/detail.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Add','30','120221','../file/icon16x16/add.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Modify','30','120222','../file/icon16x16/edit.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Remove','30','120223','../file/icon16x16/delete.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Groups','30','120290','../file/icon16x16/group.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Config','20','1203','../file/icon48x48/config.png','basic_parameter__grid.html'); 
insert into basic_permission (name,type,code,icon,path) values('Search','30','120301','../file/icon16x16/search.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Remove','30','120322','../file/icon16x16/delete.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Exercise','20','40','../file/icon48x48/paper.png','exam_paper__grid.html'); 
insert into basic_permission (name,type,code,icon,path) values('Search','30','4001','../file/icon16x16/search.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Detial','30','4002','../file/icon16x16/detail.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Upload','30','4011','../file/icon16x16/import.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Download','30','4012','../file/icon16x16/export.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Remove','30','4022','../file/icon16x16/edit.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Modify','30','4023','../file/icon16x16/delete.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Exercise','30','4090','../file/icon16x16/dopaper.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Exam','20','41','../file/icon48x48/exam.png','exam_paper_multionline__grid.html'); 
insert into basic_permission (name,type,code,icon,path) values('Search','30','4101','../file/icon16x16/search.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Detial','30','4102','../file/icon16x16/detail.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Upload','30','4111','../file/icon16x16/import.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Download','30','4112','../file/icon16x16/export.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Remove','30','4122','../file/icon16x16/edit.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Modify','30','4123','../file/icon16x16/delete.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Exam','30','4190','../file/icon16x16/dopaper.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Log','20','42','../file/icon48x48/log.png','exam_paper_log__grid.html'); 
insert into basic_permission (name,type,code,icon,path) values('Search','30','4201','../file/icon16x16/search.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Mark','30','4290','../file/icon16x16/paper_mark.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Wrongs','20','43','../file/icon48x48/question_log_wrongs.png','exam_question_log_wrongs__grid.html'); 
insert into basic_permission (name,type,code,icon,path) values('Search','30','4301','../file/icon16x16/search.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Remove','30','4322','../file/icon16x16/delete.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Exercise','30','4390','../file/icon16x16/dopaper.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Statistics','20','44','../file/icon48x48/statistics.png','exam_subject_2_user_log__statistics.html'); 
insert into basic_permission (name,type,code,icon,path) values('Subjects','20','45','../file/icon48x48/subject.png','exam_subject__grid.html'); 
insert into basic_permission (name,type,code,icon,path) values('Add','30','4521','../file/icon16x16/add.gif',''); 
insert into basic_permission (name,type,code,icon,path) values('Remove','30','4522','../file/icon16x16/delete.png',''); 
insert into basic_permission (name,type,code,icon,path) values('Config','30','4590','../file/icon16x16/subject_2_group.png',''); 
insert into basic_permission (name,type,code,icon,path) values('About','20','99','../file/icon48x48/about.png','about.html'); 









insert into basic_group_2_permission (permission_code,group_code) values ('10','10');
insert into basic_group_2_permission (permission_code,group_code) values ('11','10');
insert into basic_group_2_permission (permission_code,group_code) values ('1101','10');
insert into basic_group_2_permission (permission_code,group_code) values ('1199','10');
insert into basic_group_2_permission (permission_code,group_code) values ('12','10');
insert into basic_group_2_permission (permission_code,group_code) values ('1201','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120121','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120122','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120123','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120190','10');
insert into basic_group_2_permission (permission_code,group_code) values ('1202','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120201','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120202','10');
insert into basic_group_2_permission (permission_code,group_code) values ('12020202','10');
insert into basic_group_2_permission (permission_code,group_code) values ('12020222','10');
insert into basic_group_2_permission (permission_code,group_code) values ('12020223','10');
insert into basic_group_2_permission (permission_code,group_code) values ('1203','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120301','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120322','10');
insert into basic_group_2_permission (permission_code,group_code) values ('12020203','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120221','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120222','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120223','10');
insert into basic_group_2_permission (permission_code,group_code) values ('120290','10');
insert into basic_group_2_permission (permission_code,group_code) values ('40','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4001','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4002','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4011','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4012','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4022','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4023','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4090','10');
insert into basic_group_2_permission (permission_code,group_code) values ('41','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4101','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4102','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4111','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4112','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4122','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4123','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4190','10');
insert into basic_group_2_permission (permission_code,group_code) values ('42','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4201','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4290','10');
insert into basic_group_2_permission (permission_code,group_code) values ('43','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4301','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4322','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4390','10');
insert into basic_group_2_permission (permission_code,group_code) values ('44','10');
insert into basic_group_2_permission (permission_code,group_code) values ('45','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4521','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4522','10');
insert into basic_group_2_permission (permission_code,group_code) values ('4590','10');
insert into basic_group_2_permission (permission_code,group_code) values ('99','10');

insert into basic_group_2_permission (permission_code,group_code) values ('99','99');
insert into basic_group_2_permission (permission_code,group_code) values ('10','99');

insert into basic_group_2_permission (permission_code,group_code) values ('11','20');
insert into basic_group_2_permission (permission_code,group_code) values ('1101','20');
insert into basic_group_2_permission (permission_code,group_code) values ('1199','20');
insert into basic_group_2_permission (permission_code,group_code) values ('40','20');
insert into basic_group_2_permission (permission_code,group_code) values ('4001','20');
insert into basic_group_2_permission (permission_code,group_code) values ('4090','20');
insert into basic_group_2_permission (permission_code,group_code) values ('41','20');
insert into basic_group_2_permission (permission_code,group_code) values ('4101','20');
insert into basic_group_2_permission (permission_code,group_code) values ('4102','20');
insert into basic_group_2_permission (permission_code,group_code) values ('4190','20');
insert into basic_group_2_permission (permission_code,group_code) values ('42','20');
insert into basic_group_2_permission (permission_code,group_code) values ('4201','20');
insert into basic_group_2_permission (permission_code,group_code) values ('99','20');

insert into basic_group_2_permission (permission_code,group_code) values ('11','21');
insert into basic_group_2_permission (permission_code,group_code) values ('1101','21');
insert into basic_group_2_permission (permission_code,group_code) values ('1199','21');
insert into basic_group_2_permission (permission_code,group_code) values ('40','21');
insert into basic_group_2_permission (permission_code,group_code) values ('4001','21');
insert into basic_group_2_permission (permission_code,group_code) values ('4090','21');
insert into basic_group_2_permission (permission_code,group_code) values ('41','21');
insert into basic_group_2_permission (permission_code,group_code) values ('4101','21');
insert into basic_group_2_permission (permission_code,group_code) values ('4102','21');
insert into basic_group_2_permission (permission_code,group_code) values ('4190','21');
insert into basic_group_2_permission (permission_code,group_code) values ('42','21');
insert into basic_group_2_permission (permission_code,group_code) values ('4201','21');
insert into basic_group_2_permission (permission_code,group_code) values ('43','21');
insert into basic_group_2_permission (permission_code,group_code) values ('4301','21');
insert into basic_group_2_permission (permission_code,group_code) values ('4322','21');
insert into basic_group_2_permission (permission_code,group_code) values ('4390','21');
insert into basic_group_2_permission (permission_code,group_code) values ('44','21');
insert into basic_group_2_permission (permission_code,group_code) values ('99','21');


insert into basic_group_2_permission (permission_code,group_code) values ('11','80');
insert into basic_group_2_permission (permission_code,group_code) values ('1101','80');
insert into basic_group_2_permission (permission_code,group_code) values ('1199','80');
insert into basic_group_2_permission (permission_code,group_code) values ('40','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4001','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4002','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4011','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4012','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4022','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4023','80');
insert into basic_group_2_permission (permission_code,group_code) values ('41','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4101','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4102','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4111','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4112','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4122','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4123','80');
insert into basic_group_2_permission (permission_code,group_code) values ('42','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4201','80');
insert into basic_group_2_permission (permission_code,group_code) values ('4290','80');
insert into basic_group_2_permission (permission_code,group_code) values ('99','80');




insert into basic_group(name,code,id,type,status) values ('Manager','10','10','10','10');
insert into basic_group(name,code,id,type,status) values ('Students','20','20','20','10');
insert into basic_group(name,code,id,type,status) values ('VIP','21','21','20','10');
insert into basic_group(name,code,id,type,status) values ('Teachers','80','80','20','10');
insert into basic_group(name,code,id,type,status) values ('Guest','99','99','10','10');
insert into basic_group(name,code,id,type,status) values ('Registered','98','98','10','10');


insert into basic_user (id,username,password,money,group_code,group_all,type,status) values ('1','admin',md5('admin'),'1000','10','10','10','10');
insert into basic_user (id,username,password,money,group_code,group_all,type,status) values ('2','guest',md5('guest'),'100','99','99','10','10');
insert into basic_user (id,username,password,money,group_code,group_all,type,status) values ('3','JS801001',md5('JS801001'),'100','80','80','30','10');
insert into basic_user (id,username,password,money,group_code,group_all,type,status) values ('4','JS801002',md5('JS801002'),'100','80','80','30','10');
insert into basic_user (id,username,password,money,group_code,group_all,type,status) values ('9','XS20050101',md5('XS20050101'),'100','20','20','20','10');
insert into basic_user (id,username,password,money,group_code,group_all,type,status) values ('10','XS20050102',md5('XS20050102'),'100','21','21','20','10');


insert into basic_group_2_user (user_code,group_code) values ('admin','10');
insert into basic_group_2_user (user_code,group_code) values ('guest','99');
insert into basic_group_2_user (user_code,group_code) values ('JS801001','80');
insert into basic_group_2_user (user_code,group_code) values ('JS801002','80');
insert into basic_group_2_user (user_code,group_code) values ('XS20050101','20');
insert into basic_group_2_user (user_code,group_code) values ('XS20050102','21');

