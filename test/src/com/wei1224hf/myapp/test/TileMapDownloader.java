package com.wei1224hf.myapp.test;

import java.awt.Graphics2D;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.net.URL;

import javax.imageio.ImageIO;

public class TileMapDownloader {
	
	public static void getBaidu(int x2,int y2,int x1,int y1,int z){
		try{
			URL[][] urls = new URL[x2-x1][y2-y1]; 
			BufferedImage[][] images = new BufferedImage[x2-x1][y2-y1];
			BufferedImage bigImage = new BufferedImage(256*(x2-x1), 256*(y2-y1),BufferedImage.TYPE_INT_RGB);
			Graphics2D bigImage2 = bigImage.createGraphics();
			String path;
			for(int i=0;i<(x2-x1);i++){
				for(int j=0;j<(y2-y1);j++){
					path = "http://q7.baidu.com/it/u=x="+(i+x1)+";y="+(j+y1)+";z="+z+";v=009;type=sate&fm=46";
					System.out.println(i+" "+j+" "+path);
					urls[i][j] = new URL(path);
					images[i][j] = ImageIO.read(urls[i][j]);
					ImageIO.write(images[i][j], "png", new File("d:\\baidu\\"+i+"_"+j+".png"));
					bigImage2.drawImage(images[i][j], (i)*256, ( (y2-y1-1)-j )*256, null);
				}
			}
			ImageIO.write(bigImage, "png", new File("d:\\baidu.png"));
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
	


	public static void main(String args[]){
		//http://q5.baidu.com/it/u=x=101238;y=37702;z=19;v=009;type=sate&fm=46 北京
		TileMapDownloader.getBaidu(101238+5,37702+5,101238-5,37702-5,19);
	}
}
