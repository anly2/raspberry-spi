package sminny.remotespi.activities.utility;

import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.bluetooth.BluetoothSocket;
import android.util.Log;
import android.widget.Toast;

import java.io.IOException;
import java.util.ArrayList;
import java.util.List;
import java.util.Set;
import java.util.UUID;

import sminny.remotespi.R;

import static android.widget.Toast.makeText;

/**
 * Created by sminny on 4/26/16.
 */
public class BluetoothHelper {
    private BluetoothSocket bluetoothSocket;
    public static String DEVICE_NAME = "";
    public static String DEVICE_ADDRESS = "";
    private BluetoothDevice bluetoothDevice;

    public BluetoothHelper(UUID uuid){
        init(uuid);
    }

    private void init(UUID uuid){
        bluetoothDevice = BluetoothAdapter.getDefaultAdapter().getRemoteDevice(DEVICE_ADDRESS);
        try {
            bluetoothSocket =  bluetoothDevice.createRfcommSocketToServiceRecord(uuid);
        } catch (IOException e) {
            e.printStackTrace();
        }
    }

    public List<String> discoverDevices(){
        List<String> deviceNames = new ArrayList<>();
        Set<BluetoothDevice> devices = BluetoothAdapter.getDefaultAdapter().getBondedDevices();
        for(BluetoothDevice d : devices){
            deviceNames.add(d.getName());
            Log.d("INFO: paired device address", d.getAddress());
        }

        return deviceNames;
    }
}
