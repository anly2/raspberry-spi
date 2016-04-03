package sminny.remotespi.activities;

import android.content.Intent;
import android.support.v7.app.AppCompatActivity;
import android.os.Bundle;
import android.view.View;

import sminny.remotespi.R;

public class MainActivity extends AppCompatActivity {

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
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
