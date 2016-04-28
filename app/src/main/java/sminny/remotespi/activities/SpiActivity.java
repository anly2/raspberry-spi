package sminny.remotespi.activities;

import android.app.Activity;
import android.app.ProgressDialog;
import android.widget.Toast;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.io.IOException;

import sminny.remotespi.activities.utility.BluetoothHelper;

/**
 * Created by sminny on 4/27/16.
 */
public abstract class SpiActivity extends Activity {
    protected ProgressDialog progressDialog;
    protected BluetoothHelper bh;

    public void showProgressDialog(){
        progressDialog = ProgressDialog.show(this, "Sending request","Loading...",true,false);
    }

    public void hideProgressDialog(){
        if(progressDialog != null) {
            progressDialog.dismiss();
        }
    }

    public String constructBTRequestBody(String action, Object...args) throws JSONException {
        JSONObject obj = new JSONObject();
        obj.accumulate("action",action);
        JSONArray arr = new JSONArray();
        for(Object o : args){
            arr.put(o);
        }
        obj.accumulate("args",arr);

        return obj.toString();
    }

    public void sendMessageViaBT(String action, String...args){
        try {
            String jsonBody = constructBTRequestBody(action, args);
            showProgressDialog();
            bh.write(jsonBody);
        } catch (JSONException e) {
            e.printStackTrace();
        } catch (IOException e) {
            Toast.makeText(this, e.getMessage(), Toast.LENGTH_LONG).show();
            e.printStackTrace();
        }
    }
}
