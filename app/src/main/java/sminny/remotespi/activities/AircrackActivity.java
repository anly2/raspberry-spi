package sminny.remotespi.activities;

import android.os.Bundle;

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
}
