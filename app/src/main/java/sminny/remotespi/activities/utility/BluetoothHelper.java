package sminny.remotespi.activities.utility;

import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.bluetooth.BluetoothSocket;

import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.UUID;


/**
 * Created by sminny on 4/26/16.
 */
public class BluetoothHelper {
    private BluetoothSocket bluetoothSocket;
    public static String DEVICE_NAME = "";
    public static String DEVICE_ADDRESS = "";
    private BluetoothDevice bluetoothDevice;
    private InputStream iStream;
    private OutputStream oStream;

    public BluetoothHelper(UUID uuid){
        init(uuid);
    }

    private void init(UUID uuid){
        bluetoothDevice = BluetoothAdapter.getDefaultAdapter().getRemoteDevice(DEVICE_ADDRESS);
        try {
            bluetoothSocket =  bluetoothDevice.createRfcommSocketToServiceRecord(uuid);
            iStream = bluetoothSocket.getInputStream();
            oStream = bluetoothSocket.getOutputStream();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public void write(byte[] bytes) throws IOException {
        oStream.write(bytes);
        oStream.flush();
    }

    public int read() throws IOException {
        return iStream.read();
    }
}
