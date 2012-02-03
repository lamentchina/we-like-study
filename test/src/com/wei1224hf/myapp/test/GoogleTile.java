package com.wei1224hf.myapp.test;

import java.awt.BorderLayout;
import java.awt.Graphics2D;
import java.awt.image.BufferedImage;
import java.io.File;
import java.io.IOException;
import java.net.MalformedURLException;
import java.net.URL;

import javax.imageio.ImageIO;
import javax.swing.ImageIcon;
import javax.swing.JLabel;
import javax.swing.JOptionPane;
import javax.swing.JPanel;
import javax.swing.SwingUtilities;

public class GoogleTile {
	public static void test2() {
		try {
			// 读取第一张图片
			// File fileOne = new File(
			// "E:\\umdtask\\hz_gsq\\ms_54643_38558_16.jpg");
			File fileOne = new File(
					"D:\\Program Files\\11game\\Skin\\ImIcons\\im_team.png");
			BufferedImage ImageOne = ImageIO.read(fileOne);
			int width = ImageOne.getWidth();// 图片宽度
			int height = ImageOne.getHeight();// 图片高度
			// 从图片中读取RGB
			int[] ImageArrayOne = new int[width * height];
			ImageArrayOne = ImageOne.getRGB(0, 0, width, height, ImageArrayOne,
					0, width);
			// 对第二张图片做相同的处理
			// File fileTwo = new File(
			// "E:\\umdtask\\hz_gsq\\ms_54642_38559_16.jpg");
			File fileTwo = new File(
					"D:\\Program Files\\11game\\Skin\\ImIcons\\union_create.png");
			BufferedImage ImageTwo = ImageIO.read(fileTwo);
			int[] ImageArrayTwo = new int[width * height];
			ImageArrayTwo = ImageTwo.getRGB(0, 0, width, height, ImageArrayTwo,
					0, width);
			// 生成新图片
			BufferedImage ImageNew = new BufferedImage(width * 2, height,
					BufferedImage.TYPE_INT_RGB);
			ImageNew.setRGB(0, 0, width, height, ImageArrayOne, 0, width);// 设置左半部分的RGB
			ImageNew.setRGB(width, 0, width, height, ImageArrayTwo, 0, width);// 设置右半部分的RGB
			File outFile = new File("d:\\out.png ");
			ImageIO.write(ImageNew, "png ", outFile);// 写图片
		} catch (Exception e) {
			e.printStackTrace();
		}
	}

	public static void test1() {
		try {
		URL dayStromloUrl = new URL("http://q7.baidu.com/it/u=x=6536;y=1718;z=15;v=009;type=sate&fm=46");
		URL nightStromloUrl = new URL("http://q7.baidu.com/it/u=x=6537;y=1718;z=15;v=009;type=sate&fm=46");
		final BufferedImage dayStromloImage = ImageIO.read(dayStromloUrl);
		final BufferedImage nightStromloImage = ImageIO.read(nightStromloUrl);

		final int width = dayStromloImage.getWidth();
		final int height = dayStromloImage.getHeight();

		final BufferedImage columnImage = new BufferedImage(width, 2 * height,
				BufferedImage.TYPE_INT_RGB);
		final BufferedImage rowImage = new BufferedImage(2 * width, height,
				BufferedImage.TYPE_INT_RGB);
		
		Graphics2D g2dColumn = columnImage.createGraphics();
		g2dColumn.drawImage(dayStromloImage, 0, 0, null);
		// start this one at 'height' down the final image
		g2dColumn.drawImage(nightStromloImage, 0, height, null);

		Graphics2D g2dRow = rowImage.createGraphics();
		g2dRow.drawImage(dayStromloImage, 0, 0, null);
		// start this one at 'width' across the final image
		g2dRow.drawImage(nightStromloImage, width, 0, null);


		ImageIO.write(columnImage, "png", new File("d:\\out1.png"));
		ImageIO.write(rowImage, "png", new File("d:\\out2.png"));
		} catch (MalformedURLException e) {
			e.printStackTrace();
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
	
	public static void test3(){
		try{
			URL[] urls = new URL[10]; 
			BufferedImage[] images = new BufferedImage[10];
			BufferedImage bigImage = new BufferedImage(256*10, 250,BufferedImage.TYPE_INT_RGB);
			Graphics2D bigImage2 = bigImage.createGraphics();
			for(int i=0;i<urls.length;i++){
				urls[i] = new URL("http://q7.baidu.com/it/u=x=653"+(0+i)+";y=1718;z=15;v=009;type=sate&fm=46");
				images[i] = ImageIO.read(urls[i]);
				bigImage2.drawImage(images[i], i*256, 0, null);
			}
			ImageIO.write(bigImage, "png", new File("d:\\out1.png"));

		} catch (IOException e) {
			e.printStackTrace();
		}
	}
	
	public static void getBaidu(int x2,int y2,int x1,int y1,int z){
		try{
			URL[][] urls = new URL[x2-x1][y2-y1]; 
			BufferedImage[][] images = new BufferedImage[x2-x1][y2-y1];
			BufferedImage bigImage = new BufferedImage(256*(x2-x1), 250*(y2-y1),BufferedImage.TYPE_INT_RGB);
			Graphics2D bigImage2 = bigImage.createGraphics();
			String path;
			for(int i=0;i<(x2-x1);i++){
				for(int j=0;j<(y2-y1);j++){
					path = "http://q7.baidu.com/it/u=x="+(i+x1)+";y="+(j+y1)+";z="+z+";v=009;type=sate&fm=46";
					System.out.println(path);
					urls[i][j] = new URL(path);
					images[i][j] = ImageIO.read(urls[i][j]);
					bigImage2.drawImage(images[i][j], (i)*256, ( (y2-y1)-j )*256, null);
				}
			}
			ImageIO.write(bigImage, "png", new File("d:\\baidu.png"));
		} catch (IOException e) {
			e.printStackTrace();
		}
	}
	
	public static void getGoogle(int x2,int y2,int x1,int y1,int z){
		try{
			URL[][] urls = new URL[x2-x1][y2-y1]; 
			BufferedImage[][] images = new BufferedImage[x2-x1][y2-y1];
			BufferedImage bigImage = new BufferedImage(256*(x2-x1), 250*(y2-y1),BufferedImage.TYPE_INT_RGB);
			Graphics2D bigImage2 = bigImage.createGraphics();
			String path;
			for(int i=0;i<(x2-x1);i++){
				for(int j=0;j<(y2-y1);j++){
					path = "http://mt3.google.cn/vt/lyrs=s@102&hl=zh-CN&gl=cn&x="+(i+x1)+"&y="+(j+y1)+"&z="+z+"&s=G";
					System.out.println(path);
					urls[i][j] = new URL(path);
					images[i][j] = ImageIO.read(urls[i][j]);
					bigImage2.drawImage(images[i][j], (i)*256, ( (y2-y1)-j )*256, null);
				}
			}
			ImageIO.write(bigImage, "png", new File("d:\\google.png"));

		} catch (IOException e) {
			e.printStackTrace();
		}
	}

	public static void main(String args[]){
		GoogleTile.getGoogle(437076,215762,437066,215752,19);
	}
}
