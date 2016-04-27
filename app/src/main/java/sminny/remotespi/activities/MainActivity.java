package sminny.remotespi.activities;

import android.bluetooth.BluetoothAdapter;
import android.bluetooth.BluetoothDevice;
import android.content.Context;
import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ArrayAdapter;
import android.widget.Spinner;
import android.widget.Toast;

import java.util.ArrayList;
import java.util.Set;

import sminny.remotespi.R;
import sminny.remotespi.activities.utility.BluetoothHelper;

import static android.widget.Toast.*;

public class MainActivity extends AppCompatActivity {

    private static final int REQUEST_ENABLE_BT = 1;
    private ArrayAdapter<String> arrayAdapter;
    private Spinner spinner;
    private Context self = this;
    @Override
    protected void onDestroy(){
        super.onDestroy();
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        //check Bluetooth connectivity
        setContentView(R.layout.activity_main);
        spinner = (Spinner)findViewById(R.id.deviceSpinner);
        arrayAdapter = new ArrayAdapter<String>(this,
                android.R.layout.simple_spinner_dropdown_item, new ArrayList<String>());
        spinner.setAdapter(arrayAdapter);
        updateAdapter();
        initBluetooth();
        addActionListeners();
    }

    private void addActionListeners() {
        findViewById(R.id.refreshButton).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                Log.d("LOGGING:  ", "Refresh button clicked");
                updateAdapter();
            }
        });
        spinner.setOnItemSelectedListener(new AdapterView.OnItemSelectedListener() {
            @Override
            public void onItemSelected(AdapterView<?> parent, View view, int position, long id) {
                Set<BluetoothDevice> deviceSet = BluetoothAdapter.getDefaultAdapter().getBondedDevices();
                String selected = arrayAdapter.getItem(position);
                if(selected.equals("None"))return;

                boolean found = false;
                for(BluetoothDevice d : deviceSet){
                    if(d.getName().equals(selected)) {
                        BluetoothHelper.DEVICE_ADDRESS = d.getAddress();
                        found = true;
                    }
                }
                if(!found)
                    Toast.makeText(self, "Connection to paired device has been lost" +
                            ", please click refresh", LENGTH_LONG);
            }

            @Override
            public void onNothingSelected(AdapterView<?> parent) {
            }
        });
    }
    private void updateAdapter(){
        arrayAdapter.clear();
        Set<BluetoothDevice> deviceSet = BluetoothAdapter.getDefaultAdapter().getBondedDevices();
        if(deviceSet.size() == 0) {
            arrayAdapter.add("None");
        }else{
            for(BluetoothDevice device : deviceSet)
                arrayAdapter.add(device.getName());
        }
        BluetoothHelper.DEVICE_ADDRESS = arrayAdapter.getItem(0);
        arrayAdapter.notifyDataSetChanged();
    }
    private void initBluetooth(){
        BluetoothAdapter mBluetoothAdapter = BluetoothAdapter.getDefaultAdapter();
        if (mBluetoothAdapter == null) {
            // Device does not support Bluetooth
            makeText(this, R.string.no_bluetooth_available, LENGTH_LONG).show();
            finish();
        }

        if (!mBluetoothAdapter.isEnabled()) {
            Intent enableBtIntent = new Intent(BluetoothAdapter.ACTION_REQUEST_ENABLE);
            startActivityForResult(enableBtIntent, REQUEST_ENABLE_BT);
        }
    }
    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data){
        if(requestCode == REQUEST_ENABLE_BT && resultCode == RESULT_OK){

        }
    }
    public void openNetworkConfigurationActivity(View view) {
        Intent i = new Intent(this, NetworkConfigActivity.class);
        startActivity(i);
    }

    public void openC2ConfigServerActivity(View view) {
        Intent i = new Intent(this, CommandAndControlConfigActivity.class);
        startActivity(i);
    }

    public void executeCommandActivity(View view) {
        Intent i = new Intent(this, CommandExecutionActivity.class);
        startActivity(i);
    }


}
