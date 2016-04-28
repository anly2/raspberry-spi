package sminny.remotespi.activities.utility;

import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.bluetooth.BluetoothSocket;
import android.content.Context;
import android.os.AsyncTask;
import android.util.Log;
import android.widget.Toast;

import java.io.File;
import java.io.FileOutputStream;
import java.io.FileWriter;
import java.io.IOException;
import java.io.InputStream;
import java.io.OutputStream;
import java.util.UUID;

import sminny.remotespi.activities.SpiActivity;


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
    private UUID uuid;
    private SpiActivity activity;
    private boolean isCommunicating = false;

    public BluetoothHelper(SpiActivity activity){
        this.activity = activity;
        uuid = UUID.fromString("94f39d29-7d6d-437d-973b-fba39e49d4ee");
        init();
    }

    private void init(){
        Log.d("LOGGING: ", DEVICE_ADDRESS + " " + DEVICE_NAME);
        bluetoothDevice = BluetoothAdapter.getDefaultAdapter().getRemoteDevice(DEVICE_ADDRESS);
        updateSocketAndStreams();
    }

    private void updateSocketAndStreams() {
        try {
            bluetoothSocket =  bluetoothDevice.createRfcommSocketToServiceRecord(uuid);
            BluetoothAdapter.getDefaultAdapter().cancelDiscovery();

            iStream = bluetoothSocket.getInputStream();
            oStream = bluetoothSocket.getOutputStream();
        } catch (IOException e) {
            e.printStackTrace();
        }
    }
    public synchronized void fetchFile(){
        BluetoothDownloadFileTask bdft = new BluetoothDownloadFileTask();
        bdft.execute();
    }
    public synchronized void write(String s) throws IOException {
        if(isCommunicating)
            throw new IOException("Bluetooth module is currently communicating, try again later");
        BluetoothConnectionTask task = new BluetoothConnectionTask();
        task.execute(s);
    }

    public int read() throws IOException {
        return iStream.read();
    }

    private class BluetoothDownloadFileTask extends AsyncTask<String, Void, byte[]>{
        @Override
        protected void onPostExecute(byte[] result){

            isCommunicating = false;
            if(result == null) {
                Toast.makeText(activity, "Device Bluetooth connection not found," +
                        " please check it and try again later", Toast.LENGTH_LONG).show();
                return;
            }
            else {
                Toast.makeText(activity, "Successfully sent command to device", Toast.LENGTH_LONG).show();
            }
            String s = "";
            for(int i = 0; i<32;i++){
                s += result[i];
            }
            System.out.println("HEADER IS: " + s);
            activity.hideProgressDialog();
        }

        @Override
        protected byte[] doInBackground(String... params) {
            try {
                updateSocketAndStreams();
                bluetoothSocket.connect();
                if(bluetoothSocket.isConnected()) {
                    for(String s : params){
                        oStream.write(s.getBytes());
                        oStream.flush();
                    }
                    byte nul = 0x00;
                    oStream.write(nul);
                    oStream.flush();
                    String s = "";
                    byte[] buf = new byte[1024];
                    while(iStream.read(buf) != -1)
                        s += buf;

                    oStream.close();
                    iStream.close();
                    bluetoothSocket.close();
                    return s.getBytes();
                }
            } catch (IOException e) {
                e.printStackTrace();
            }

            return null;
        }

        private void saveFile(byte[] arr){
            File f = new File("whatever");
            try {
                FileOutputStream fw =  new FileOutputStream(f);
                fw.write(arr);
            } catch (IOException e) {
                e.printStackTrace();
            }
        }
    }
    private class BluetoothConnectionTask extends AsyncTask<String,Void, String>{

        @Override
        public void  onPostExecute(String result){
            if(result == null)
                Toast.makeText(activity, "Device Bluetooth connection not found," +
                        " please check it and try again later", Toast.LENGTH_LONG).show();
            else
                Toast.makeText(activity, "Successfully sent command to device", Toast.LENGTH_LONG).show();
            isCommunicating = false;
            activity.hideProgressDialog();
        }

        @Override
        protected String doInBackground(String... params) {
            try {
                updateSocketAndStreams();
                bluetoothSocket.connect();
                if(bluetoothSocket.isConnected()) {
                    for(String s : params){
                        oStream.write(s.getBytes());
                        oStream.flush();
                    }
//                    byte nul = 0x00;
//                    oStream.write(nul);
//                    oStream.flush();

                    oStream.close();
                    iStream.close();
                    bluetoothSocket.close();
                    return "success";
                }
            } catch (IOException e) {
                e.printStackTrace();
            }
            return null;
        }
    }
}
