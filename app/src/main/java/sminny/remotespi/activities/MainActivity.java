package sminny.remotespi.activities;

import android.bluetooth.BluetoothAdapter;
import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;

import sminny.remotespi.R;

import static android.widget.Toast.*;

public class MainActivity extends AppCompatActivity {

    private static final int REQUEST_ENABLE_BT = 1;
    private boolean isPaired = false;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        //check Bluetooth connectivity
        setContentView(R.layout.activity_main);
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
    public void onActivityResult(int requestCode, int resultCode, Intent data){
        if(requestCode == REQUEST_ENABLE_BT && resultCode == RESULT_OK){
            isPaired = true;
        }
    }
    public void openNetworkConfigurationActivity(View view) {
        if(isPaired) {
            Intent i = new Intent(this, NetworkConfigActivity.class);
            startActivity(i);
        }
    }

    public void openC2ConfigServerActivity(View view) {
        if(isPaired) {
            Intent i = new Intent(this, CommandAndControlConfigActivity.class);
            startActivity(i);
        }
    }

    public void executeCommandActivity(View view) {
        if(isPaired) {
            Intent i = new Intent(this, CommandExecutionActivity.class);
            startActivity(i);
        }
    }
}
