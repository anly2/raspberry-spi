package sminny.remotespi.activities;

import android.app.Activity;
import android.content.Context;
import android.os.Bundle;
import android.view.View;
import android.view.inputmethod.InputMethodManager;
import android.widget.EditText;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

import sminny.remotespi.R;
import sminny.remotespi.activities.utility.BluetoothHelper;

public class NetworkConfigActivity extends Activity {
    private BluetoothHelper bh;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        bh = new BluetoothHelper(this);
        setContentView(R.layout.activity_network_config);
    }

    public void sendNetworkConfigurationCommand(View view) {
        String essid = ((EditText)findViewById(R.id.networkNameField)).getText().toString();
        String passwd = ((EditText)findViewById(R.id.networkPasswordField)).getText().toString();

        View v = this.getCurrentFocus();
        if (v != null) {
            InputMethodManager imm = (InputMethodManager)getSystemService(Context.INPUT_METHOD_SERVICE);
            imm.hideSoftInputFromWindow(view.getWindowToken(), 0);
        }

        JSONObject json = new JSONObject();
        try {
            JSONArray array = new JSONArray();
            array.put(essid);
            array.put(passwd);
            json.accumulate("action", "config_network");
            json.accumulate("args", array);
            bh.write(json.toString());
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }
}
