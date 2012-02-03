package com.wei1224hf.myapp.test;

import java.io.File;
import java.awt.image.BufferedImage;
import javax.imageio.ImageIO;

public class GoogleTile {
	public static void main(String args[]) {
		try {
			// 读取第一张图片
//			File fileOne = new File(
//					"E:\\umdtask\\hz_gsq\\ms_54643_38558_16.jpg");
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
//			File fileTwo = new File(
//					"E:\\umdtask\\hz_gsq\\ms_54642_38559_16.jpg");
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

}
