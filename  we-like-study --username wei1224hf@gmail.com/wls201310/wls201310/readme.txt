独立安装:
运行 php/install.php ,mode选择WLS

DZX 集成安装:
http://hi.baidu.com/w2y0xy0sf5z/item/8068e46c6b5eb82368105b66

疑问,咨询QQ群: 135426431



//////////////////////////////
本版本附带手机端程序,手机端程序刚开发,不太稳定,计算机基础不深的,不要使用

wls3.6.1.apk 为android手机端程序,在运行的时候,需要在手机的 SD 卡里,新建一个 wls/config.xml 文件,并写上你的配置参数,比如：
<?xml version="1.0" encoding="UTF-8"?>
<MYAPP>
	<PATH ID="PATH"><![CDATA[http://112.10.42.7:8071/dzx/upload/wls/php/myapp.php]]></PATH>	
	<IL8N ID="IL8N">zh-cn</IL8N>	
</MYAPP>
其中的PATH为你自己的WLS路径


