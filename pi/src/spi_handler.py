from bluetooth import *

def dispatch(request):
    print "dispatched"

def establishBTSocket():
    server_sock=BluetoothSocket( RFCOMM )
    server_sock.bind(("",PORT_ANY))
    server_sock.listen(1)

    port = server_sock.getsockname()[1]

    uuid = "94f39d29-7d6d-437d-973b-fba39e49d4ee"

    advertise_service( server_sock, "RPiServer",
                       service_id = uuid,
                       service_classes = [ uuid, BASIC_PRINTING_CLASS ],
                       profiles = [ BASIC_PRINTING_PROFILE ],
    #                   protocols = [ OBEX_UUID ]
                        )
    return server_sock, port;

def bindConnection(server_sock, port):
    print("Waiting for connection on RFCOMM channel %d" % port)

    client_sock, client_info = server_sock.accept()
    client_sock.settimeout(1.0)
    print("Accepted connection from ", client_info)
    data = ""
    try:
        while True:
            d = client_sock.recv(1024)
            if len(d) == 0:
                print "should disconnect"
                break
            data += d
            print("received [%s]" % data)


        print "disconnected"
    except IOError:
        print "Connection error or timeout..."
        #return None, data


    #print "Sending response length to client and returning result..."
    #client_sock.send(str(len(data)))
    #client_sock.close()
    print("all done")
    return data

sock, port = establishBTSocket()

while(True):
    request = bindConnection(sock, port)
    if request != "":
        dispatch(request)
