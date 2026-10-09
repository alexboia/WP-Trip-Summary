import numpy as np
from cv2.typing import MatLike
import cv2

def wpts_apply_soften_filter(inputImage: MatLike, diameter=12, sigmaSpace=100) -> MatLike:
	outputImage = cv2.bilateralFilter(inputImage, d=diameter, sigmaColor=75, sigmaSpace=sigmaSpace)
	return outputImage

def wpts_apply_vignette_filter(inputImage: MatLike, size:float=0.65) -> MatLike:
	rows, cols = inputImage.shape[:2]

	if (rows < 200 or cols < 200):
		return inputImage

	sigmaX = cols * size
	sigmaY = rows * size

	xResultantKernel = cv2.getGaussianKernel(cols, sigmaX)
	yResultantKernel = cv2.getGaussianKernel(rows, sigmaY)

	mask = np.outer(yResultantKernel, xResultantKernel)
	maskNormalized = mask / mask.max()
	
	outputImage = np.zeros_like(inputImage, dtype=np.uint8)
	for i in range(3):
		outputImage[:, :, i] = inputImage[:, :, i] * maskNormalized

	return outputImage