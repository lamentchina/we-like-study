create or replace procedure nst_person_data4test(total int) is
first_names     varchar(200);
last_names      varchar2(1000);
nationality_    varchar(200);
nation_         varchar(200);
degree_         varchar(200);
birthplace_     varchar(200);
i               NUMBER;
begin
  first_names := '李王张刘陈杨赵黄周吴徐孙胡朱高林何郭马罗梁宋郑谢韩唐冯于董肖程曹袁邓许傅沈曾彭吕苏卢蒋贾丁魏薛叶阎余潘杜戴夏钟汪田任姜范方石姚谭廖邹熊金陆郝孔白崔康毛邱秦江史顾侯邵孟龙万段雷钱汤尹黎易常武乔贺赖龚文';
  last_names := '安邦安福安歌安国安和安康安澜安民安宁安平安然安顺安翔安晏安宜安怡安易安志昂然昂雄宾白宾鸿宾实彬彬彬炳彬郁斌斌斌蔚滨海波光波鸿波峻波涛博瀚博超博达博厚博简博明博容博赡博涉博实博涛博文博学博雅博延博艺博易博裕博远才捷才良才艺才英才哲才俊成和成弘成化成济成礼成龙成仁成双成天成文成业成益成荫成周承安承弼承德承恩承福承基承教承平承嗣承天承望承宣承颜承业承悦承允承运承载承泽承志德本德海德厚德华德辉德惠德容德润德寿德水德馨德曜德业德义德庸德佑德宇德元德运德泽德明飞昂飞白飞飙飞掣飞尘飞沉飞驰飞光飞翰飞航飞翮飞鸿飞虎飞捷飞龙飞鸾飞鸣飞鹏飞扬飞文飞翔飞星飞翼飞英飞宇飞羽飞雨飞语飞跃飞章飞舟风华丰茂丰羽刚豪刚洁刚捷刚毅高昂高岑高畅高超高驰高达高澹高飞高芬高峯高峰高歌高格高寒高翰高杰高洁高峻高朗高丽高邈高旻高明高爽高兴高轩高雅高扬高阳高义高谊高逸高懿高原高远高韵高卓光赫光华光辉光济光霁光亮光临光明光启光熙光耀光誉光远国安国兴国源冠宇冠玉晗昱晗日涵畅涵涤涵亮涵忍涵容涵润涵涵涵煦涵蓄涵衍涵意涵映涵育翰采翰池翰飞翰海翰翮翰林翰墨翰学翰音瀚玥翰藻瀚海瀚漠昊苍昊昊昊空昊乾昊穹昊然昊然昊天';
  
  for i in 1..total loop
      select value into nationality_ from (select std.value from nst_standards std 
                           where std.source = 'GB2659'  order by dbms_random.value)temp where rownum < 2;
      select value into nation_ from (select std.value from nst_standards std 
                           where std.source = 'GB3304'  order by dbms_random.value)temp where rownum < 2;                           
      select code into degree_ from (select std.code from nst_standards std 
                           where std.source = 'www.51job.com' and txt1='degree.resume' order by dbms_random.value)temp where rownum < 2;     
      select code into birthplace_ from (select std.code from nst_standards std 
                           where std.source = 'GB2260'  order by dbms_random.value)temp where rownum < 2;                                                          
      insert into nst_person(
          guid
          ,name
          ,gender
          ,birthday
          ,birthplace
          ,nationality
          ,nation
          ,degree
          ,height
          ,phone
      )values(
          createGUID()
          ,( select SUBSTR(first_names,abs(mod(dbms_random.random,length(first_names)-2)),1) from dual  )||( select SUBSTR(last_names,abs(mod(dbms_random.random,length(last_names)-3)),2) from dual  )
          ,( select (abs(mod(dbms_random.random,2))+1) from dual  )
          ,( select to_date(TO_CHAR(SYSDATE-abs(mod(dbms_random.random,365*30))-365*18 , 'YYYY-MM-DD'),'YYYY-MM-DD') from dual )
          ,birthplace_
          ,nationality_
          ,nation_
          ,degree_
          ,( select (abs(mod(dbms_random.random,40))+140) from dual  )
          ,'13511111111'
      );      
     
  end loop; 
  commit;
end nst_person_data4test;
/
