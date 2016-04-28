package sminny.remotespi.activities;

import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

import sminny.remotespi.R;
import sminny.remotespi.activities.utility.BluetoothHelper;

public class NmapActivity extends SpiActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        bh = new BluetoothHelper(this);
        setContentView(R.layout.activity_nmap);
    }

    public void executeStealthScan(View view) {
        String address = ((EditText)findViewById(R.id.addressField)).getText().toString();
        String subnet = ((EditText)findViewById(R.id.subnetField)).getText().toString();

        sendMessageViaBT("nmap_sS", address, subnet);

    }
}
