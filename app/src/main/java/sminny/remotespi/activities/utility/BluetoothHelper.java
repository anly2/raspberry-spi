package sminny.remotespi.activities.utility;

import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.bluetooth.BluetoothSocket;
import android.media.MediaScannerConnection;
import android.os.AsyncTask;
import android.os.Environment;
import android.util.Log;
import android.widget.Toast;

import java.io.File;
import java.io.FileOutputStream;
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
    public synchronized void fetchFile(String s){
        BluetoothDownloadFileTask bdft = new BluetoothDownloadFileTask();
        bdft.execute(s);
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
            activity.hideProgressDialog();
            Log.d("TAGGGG",String.valueOf(result.length));
            if(result.length < 3) {
                Toast.makeText(activity, "Device Bluetooth connection not found," +
                        " please check it and try again later", Toast.LENGTH_LONG).show();
                return;
            }else if(result.length == 3) {
                Toast.makeText(activity, "No file present on device", Toast.LENGTH_LONG).show();
                return;
            }else {
                Toast.makeText(activity, "Successfully saved file", Toast.LENGTH_LONG).show();
            }
            saveFile(result);
        }

        @Override
        protected byte[] doInBackground(String... params) {
            String s = "";
            long c = 0l;
            try {
                updateSocketAndStreams();
                bluetoothSocket.connect();
                if(bluetoothSocket.isConnected()) {
                    for(String str : params){
                        oStream.write(str.getBytes());
                        oStream.flush();
                    }
                    byte nul = 0x00;
                    oStream.write(nul);
                    oStream.flush();
                    int b;
                    String loaded = "Loaded ";
                    String bytes = " bytes";
                    Thread.sleep(2000);
                    while((b = iStream.read()) != -1) {
                        Log.d("READ",String.valueOf(b));
                        activity.updateLoadingProgress(loaded + c + bytes);
                        s += (char) b;
                        c++;
                    }

                    oStream.close();
                    iStream.close();
                    bluetoothSocket.close();
                    return s.getBytes();
                }
            } catch (IOException e) {
                e.printStackTrace();
                if(c == 1) {
                    //file does not exist
                    return new byte[]{0x00};
                }
                return s.getBytes();
            } catch (InterruptedException e) {
                e.printStackTrace();
            }

            return null;
        }

        private void saveFile(byte[] arr){

            File f = new File(Environment.getExternalStoragePublicDirectory(
                    Environment.DIRECTORY_DOWNLOADS),"RSpi");
            if (!f.mkdirs()) {
                Log.e("ERR", "Directory not created");
            }
            try {
//                f.setReadable(true, false);
                File tmp = new File(f, "dump"+System.currentTimeMillis());
                FileOutputStream fw =  new FileOutputStream(tmp);
                fw.write(arr);
                fw.flush();
                fw.close();

                MediaScannerConnection.scanFile(activity, new String[]{ f.toString()}, null, null);
            } catch (IOException e) {
                e.printStackTrace();
            }
            Toast.makeText(activity, f.getAbsolutePath(), Toast.LENGTH_LONG).show();
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
                    String input = "";
                    int i = 0;
                    while(iStream.available() < 4 && i < 10){
                        Log.d("STREAM","reading from stream");
                        Thread.sleep(300);
                        i++;
                    }
                    Log.d("OOUT","out");
                    if(i != 10){
                        int ch = 0;
                        while((ch = iStream.read()) != -1){
                            input += (char)ch;
                        }
                        if(input.equals("retransmit")) {
                            oStream.close();
                            iStream.close();
                            bluetoothSocket.close();
                            return doInBackground(params);
                        }
                    }
                    oStream.close();
                    iStream.close();
                    bluetoothSocket.close();
                    return "success";
                }
            } catch (IOException e) {
                e.printStackTrace();
            } catch (InterruptedException e) {
                e.printStackTrace();
            }
            return null;
        }
    }
}
