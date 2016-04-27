package sminny.remotespi.activities;

import android.app.Activity;
import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.Toast;

import org.json.JSONException;

import java.io.IOException;

import sminny.remotespi.R;
import sminny.remotespi.activities.utility.BluetoothHelper;

public class CommandAndControlConfigActivity extends SpiActivity {
    private BluetoothHelper bh;
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

        try {
            String json = constructBTRequestBody("config_c2", address, beaconMethod, port, identifier);
            showProgressDialog();
            bh.write(json);
        } catch (JSONException e) {
            e.printStackTrace();
        } catch (IOException e) {
            Toast.makeText(this, e.getMessage(), Toast.LENGTH_LONG).show();
            e.printStackTrace();
        }

    }
}
