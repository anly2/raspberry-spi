package sminny.remotespi.activities;

import android.app.Activity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.Toast;

import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

import sminny.remotespi.R;
import sminny.remotespi.activities.utility.BluetoothHelper;

public class CommandAndControlConfigActivity extends SpiActivity {
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        bh = new BluetoothHelper(this);
        setContentView(R.layout.activity_command_and_control_config);
    }

    public void executeC2Configuration(View view) {

        String address = ((EditText)findViewById(R.id.addressField)).getText().toString();
        String beaconMethod = ((Spinner)findViewById(R.id.beaconMethodSpinner)).getSelectedItem().toString();
        String port = ((EditText)findViewById(R.id.portField)).getText().toString();
        String identifier= ((EditText)findViewById(R.id.identifierField)).getText().toString();
        JSONObject obj = new JSONObject();
        try {
            obj.accumulate("address",address);
            obj.accumulate("beaconMethod",beaconMethod);
            obj.accumulate("port",port);
            obj.accumulate("identifier",identifier);
        } catch (JSONException e) {
            e.printStackTrace();
        }
        sendMessageViaBT("config_c2", obj);

    }
}
