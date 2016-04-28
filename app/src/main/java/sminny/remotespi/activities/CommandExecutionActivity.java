package sminny.remotespi.activities;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.util.Log;
import android.view.View;
import android.widget.AdapterView;
import android.widget.ListView;

import sminny.remotespi.R;

public class CommandExecutionActivity extends SpiActivity{
    private Context self;
    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        self = this;
        setContentView(R.layout.activity_command_execution);
        final ListView listView = (ListView) findViewById(R.id.listView);

        listView.setOnItemClickListener(new AdapterView.OnItemClickListener() {
            @Override
            public void onItemClick(AdapterView<?> parent, View view, int position, long id) {
                String chosenOne = listView.getAdapter().getItem(position).toString();
                Log.d("SELECTED: ",chosenOne);
                switch(chosenOne){
                    case "airodump-ng":
                        Intent aircrackIntent = new Intent(self, AircrackActivity.class);
                        startActivity(aircrackIntent);
                        break;
                    case "nmap - Stealth scan":
                        Intent nmapIntent = new Intent(self, NmapActivity.class);
                        startActivity(nmapIntent);
                        break;
                    case "ping":
                        Intent ping = new Intent(self, PingActivity.class);
                        startActivity(ping);
                        break;
                    case "Download pcap file":
                        bh.fetchFile();
                        break;

                }

            }
        });
    }
}
