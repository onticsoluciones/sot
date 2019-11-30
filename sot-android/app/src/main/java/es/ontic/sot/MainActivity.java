package es.ontic.sot;

import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.NotificationCompat;
import androidx.core.app.NotificationManagerCompat;

import android.annotation.SuppressLint;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.os.Build;
import android.os.Bundle;
import android.util.Base64;
import android.view.View;
import android.widget.ArrayAdapter;
import android.widget.Button;
import android.widget.EditText;
import android.widget.Spinner;
import android.widget.Toast;

import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.TimeoutError;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

import es.ontic.sot.model.Alert;
import es.ontic.sot.parser.AlertParser;

public class MainActivity extends AppCompatActivity
{
    private static final String CHANNEL_ID = "SoT";
    private long startTime = System.currentTimeMillis() / 1000;

    EditText uiHost;
    EditText uiUser;
    EditText uiPassword;
    Spinner uiPriority;
    Button uiConnect;

    @Override
    protected void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        createNotificationChannel();

        uiHost = findViewById(R.id.host);
        uiUser = findViewById(R.id.user);
        uiPassword = findViewById(R.id.password);
        uiPriority = findViewById(R.id.priority);
        uiConnect = findViewById(R.id.connect);

        uiHost.setText(getPreferences(MODE_PRIVATE).getString("host", ""));
        uiUser.setText(getPreferences(MODE_PRIVATE).getString("user", ""));
        uiPassword.setText(getPreferences(MODE_PRIVATE).getString("password", ""));

        final ArrayAdapter adapter = new ArrayAdapter<>(
            this,
            android.R.layout.simple_spinner_dropdown_item,
            new String[] { "LOW", "NORMAL", "HIGH", "CRITICAL" }
        );
        uiPriority.setAdapter(adapter);

        uiHost.addTextChangedListener(new PersistentTextWatcher(this, "host"));
        uiUser.addTextChangedListener(new PersistentTextWatcher(this, "user"));
        uiPassword.addTextChangedListener(new PersistentTextWatcher(this, "password"));

        uiConnect.setOnClickListener(new View.OnClickListener()
        {
            @Override
            public void onClick(View v)
            {
                if(uiConnect.getText() == getString(R.string.connect))
                {
                    uiConnect.setText(R.string.disconnect);
                    getAlerts(
                        uiHost.getText().toString(),
                        uiUser.getText().toString(),
                        uiPassword.getText().toString(),
                        getTimestamp()
                    );
                }
                else
                {
                    android.os.Process.killProcess(android.os.Process.myPid());
                }
            }
        });

    }

    private void createNotificationChannel()
    {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O)
        {
            CharSequence name = getString(R.string.channel_name);
            String description = getString(R.string.channel_description);
            int importance = NotificationManager.IMPORTANCE_DEFAULT;
            NotificationChannel channel = new NotificationChannel(CHANNEL_ID, name, importance);
            channel.setDescription(description);
            NotificationManager notificationManager = getSystemService(NotificationManager.class);
            notificationManager.createNotificationChannel(channel);
        }
    }


    private void getAlerts(final String host, final String user, final String password, final Long timestamp)
    {
        RequestQueue queue = Volley.newRequestQueue(this);

        String url = host;
        if(timestamp != null)
        {
            url += "?from=" + timestamp;
        }

        StringRequest stringRequest = new StringRequest(Request.Method.GET, url,
                new Response.Listener<String>()
                {
                    @Override
                    public void onResponse(String response)
                    {
                        Long lastTimestamp = timestamp;

                        try
                        {
                            final List<Alert> alerts = new AlertParser().parseAll(response);
                            displayAlerts(alerts);
                            lastTimestamp = getMostRecentTimestamp(alerts, timestamp);
                            saveTimestamp(lastTimestamp);
                        }
                        catch (JSONException e)
                        {
                            e.printStackTrace();
                        }
                        finally
                        {
                            getAlerts(host, user, password, lastTimestamp);
                        }
                    }
                }, new Response.ErrorListener()
        {
            @Override
            public void onErrorResponse(VolleyError error)
            {
                if(error instanceof TimeoutError)
                {
                    getAlerts(host, user, password, timestamp);
                }
                else
                {
                    Toast.makeText(MainActivity.this, error.getLocalizedMessage(), Toast.LENGTH_LONG).show();
                    uiConnect.setText(R.string.connect);
                }
            }
        }) {
            @Override
            public Map<String, String> getHeaders()
            {
                Map<String, String> params = new HashMap<>();
                params.put(
                        "Authorization",
                        String.format("Basic %s", Base64.encodeToString(
                                String.format("%s:%s", user, password).getBytes(), Base64.DEFAULT)));

                return params;
            }
        };

        stringRequest.setRetryPolicy(new DefaultRetryPolicy(600000,
            DefaultRetryPolicy.DEFAULT_MAX_RETRIES,
            DefaultRetryPolicy.DEFAULT_BACKOFF_MULT));

        queue.add(stringRequest);
    }

    private Long getMostRecentTimestamp(List<Alert> alerts, Long defaultTimestamp)
    {
        Long timestamp = defaultTimestamp;

        for(Alert alert : alerts)
        {
            if(timestamp == null || alert.getTimestamp() > timestamp)
            {
                timestamp = alert.getTimestamp();
            }
        }

        return timestamp;
    }

    private void displayAlerts(List<Alert> alerts)
    {
        for (final Alert alert : alerts)
        {
            displayAlert(alert);
        }
    }

    private void displayAlert(Alert alert)
    {
        final int alertLevel = uiPriority.getSelectedItemPosition();

        if(alert.getPriority() < alertLevel)
        {
            return;
        }

        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, CHANNEL_ID)
                .setSmallIcon(android.R.drawable.stat_sys_warning)
                .setContentTitle(getAlertTitle(alert.getType()))
                .setContentText(getAlertText(alert.getType(), alert.getDevice()))
                .setStyle(new NotificationCompat.BigTextStyle()
                        .bigText(getAlertText(alert.getType(), alert.getDevice())))
                .setPriority(NotificationCompat.PRIORITY_DEFAULT);


        NotificationManagerCompat notificationManager = NotificationManagerCompat.from(this);
        notificationManager.notify(getAlertId(alert), builder.build());
    }

    private int getAlertId(Alert alert)
    {
        int id = (int) (alert.getTimestamp() - startTime);
        id += alert.getType().hashCode();
        if(alert.getDevice() != null)
        {
            id += alert.getDevice().hashCode();
        }

        return id;
    }

    private String getAlertTitle(String type)
    {
        if(Alert.TYPE_NEW_DEVICE.equals(type))
        {
            return "Unknown device";
        }
        else if(Alert.TYPE_INTERMITTENT_POWER.equals(type))
        {
            return "Reboot loop";
        }
        else if(Alert.TYPE_ACTIVATION.equals(type))
        {
            return "Device activation";
        }
        else if(Alert.TYPE_DANGEROUS_ACTIVATION.equals(type))
        {
            return "Dangerous device activation";
        }
        else
        {
            return type;
        }
    }

    private String getAlertText(String type, String device)
    {
        if(Alert.TYPE_NEW_DEVICE.equals(type))
        {
            return String.format("An unknown device \"%s\" has been detected on the network.", device);
        }
        else if(Alert.TYPE_INTERMITTENT_POWER.equals(type))
        {
            return String.format("Device \"%s\" has been restarted multiple times in a short while.", device);
        }
        else if(Alert.TYPE_ACTIVATION.equals(type))
        {
            return String.format("Device \"%s\" has been activated.", device);
        }
        else if(Alert.TYPE_DANGEROUS_ACTIVATION.equals(type))
        {
            return String.format("Device \"%s\" has been unexpectedly activated.", device);
        }
        else
        {
            return String.format("Se ha producido una incidencia con el dispositivo \"%s\".", device);
        }
    }

    private Long getTimestamp()
    {
        final long timestamp = getPreferences(MODE_PRIVATE).getLong("timestamp", 0);
        return timestamp > 0 ? timestamp : null;
    }

    @SuppressLint("ApplySharedPref")
    private void saveTimestamp(Long timestamp)
    {
        if(timestamp != null)
        {
            getPreferences(MODE_PRIVATE)
                .edit()
                .putLong("timestamp", timestamp)
                .commit();
        }
    }
}
