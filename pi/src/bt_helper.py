from bluetooth import *

CLIENT_SOCKET = None

def dispatch(client_sock, request):
    print "dispatched"

    if client_sock is not None:
        fileContent = ""
        with open("text.txt","rb") as f:
            fileContent = f.read()
        client_sock.send(fileContent)
        client_sock.close()

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
    #client_sock.settimeout(1.0)
    print("Accepted connection from ", client_info)
    data = ""
    try:
        while True:
            d = client_sock.recv(1024)
            print "D is"
            print d
            if len(d) == 1:
                print "stateful BT connection"
                return client_sock, data
 
            data += d
            print("received [%s]" % data)


        print "disconnected"
    except IOError as e:
        print "I/O error({0}): {1}".format(e.errno, e.strerror)


    print("all done")
    return None, data

if __name__ == '__main__':
    sock, port = establishBTSocket()
    while(True):
        client_sock, request = bindConnection(sock, port)
        dispatch(client_sock, request)