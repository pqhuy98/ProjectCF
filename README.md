# ProjectCF
A neural network with web-interface.  
Cleaned implementation of https://pqhuy98.hopto.org/ProjectCF/ .  

How it works :  
1)  You upload an image.  
2)  PHP saves it and sends its path to the socket server on port 6969.  
3)  Socket server listens on port 6969, receive all the path, read all images and feeds them to a CNN, then sends the result back to PHP.
4)  PHP decode it and generate the result page.

Simple, right ?
