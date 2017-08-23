import numpy as np
import matplotlib.pyplot as plt
from keras.models import load_model
from scipy.misc import imread,imresize
import socket
import threading
from PIL import Image

imagepath = "image.jpeg";

def show(im,name=""):
    plt.figure()
    plt.title(name)
    plt.imshow(im)
    plt.show()
    
def toRGB(im, bg_colour=(255, 255, 255)):
    if im.mode in ('RGBA', 'LA') or (im.mode == 'P' and 'transparency' in im.info):
	print "toRGB !"
        alpha = im.convert('RGBA').split()[-1]
        bg = Image.new("RGBA", im.size, bg_colour + (255,))
        bg.paste(im, mask=alpha)
        return bg

    else:
        return im

def crop_and_resize(input_path,height=32,width=32) :
    img = Image.open(input_path)

    width,height = img.size

    mx = max(width,height)
    mn = min(width,height)
    left = (mx-height)/2
    top = (mx-width)/2
    right = width-(left+mn)
    bottom = height-(top+mn)

    img = img.crop((left,top,width-right,height-bottom))

    img = img.convert("RGB")
    img = toRGB(img) 

    img.thumbnail((32,32),Image.ANTIALIAS)
    #show(img)
    
    width, height = img.size
    img = np.reshape(img.getdata(),(32,32,3))
    return img

labels = np.loadtxt('labels.txt', str, delimiter='\n');
print "Loading model"
model = load_model("net.h5")

def get_result(path) :
    print "Rescaling image",path
    img = crop_and_resize(path)
    img = np.transpose(img,axes=(2,0,1)).astype("float32")
    img/=255
    res = ""
    print "Feed forward..."
    pred = model.predict(np.array([img]))
    print labels[np.argmax(pred)]
    for i in range(pred.shape[1]) :
        x = pred[0,i]
        res+=str(round(x,3))+" "
    return res[:-1]

def solve(sock) :
    path = sock.recv(1000000)
    msg = get_result(path)
    sock.send(msg)
    sock.close()
    print msg
    print "Closed"
    print 

print "Creating socket..."
sock = socket.socket()
sock.bind(("",2222))
sock.listen(100)
print "Waiting for connection..."
try :
    while True :
        (client_sock,address) = sock.accept()
        print "Connected by %s"%str(address)
        solve(client_sock)
except KeyboardInterrupt:
    print "Connection closed."
    sock.close()
sock.close()
