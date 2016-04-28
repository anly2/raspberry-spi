package sminny.remotespi.activities;

import android.os.Bundle;
import android.view.View;
import android.widget.EditText;

import sminny.remotespi.R;
import sminny.remotespi.activities.utility.BluetoothHelper;

public class AircrackActivity extends SpiActivity {
    private BluetoothHelper bh;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        bh = new BluetoothHelper(this);
        setContentView(R.layout.activity_aircrack);
    }

    public void executeAirodumpCommand(View view) {
        String channels = ((EditText)findViewById(R.id.channelsField)).getText().toString();
        String bssid = ((EditText)findViewById(R.id.bssidField)).getText().toString();

        sendMessageViaBT("airodump", channels, bssid);

    }

    public void stopAirodumpCommand(View view) {
        sendMessageViaBT("stop_airodump");
    }
}
