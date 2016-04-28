package sminny.remotespi.activities;

import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

import sminny.remotespi.R;
import sminny.remotespi.activities.utility.BluetoothHelper;

public class PingActivity extends SpiActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        bh = new BluetoothHelper(this);
        setContentView(R.layout.activity_ping);
    }

    public void executePingCommand(View view) {
        String address = ((EditText)findViewById(R.id.addressField)).getText().toString();
        String count = ((EditText)findViewById(R.id.countField)).getText().toString();
        String interval = ((EditText)findViewById(R.id.intervalField)).getText().toString();
        String ttl = ((EditText)findViewById(R.id.ttlField)).getText().toString();

        sendMessageViaBT("ping", address, count, interval, ttl);
    }
}
