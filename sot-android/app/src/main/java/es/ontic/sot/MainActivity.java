package es.ontic.sot;

import androidx.appcompat.app.AppCompatActivity;
import androidx.core.app.NotificationCompat;
import androidx.core.app.NotificationManagerCompat;

import android.annotation.SuppressLint;
import android.app.NotificationChannel;
import android.app.NotificationManager;
import android.os.Build;
import android.os.Bundle;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;

import com.android.volley.DefaultRetryPolicy;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.Response;
import com.android.volley.VolleyError;
import com.android.volley.toolbox.StringRequest;
import com.android.volley.toolbox.Volley;

import org.json.JSONException;

import java.util.List;

import es.ontic.sot.model.Alert;
import es.ontic.sot.parser.AlertParser;

public class MainActivity extends AppCompatActivity
{
    private static final String CHANNEL_ID = "SoT";
    private long startTime = System.currentTimeMillis() / 1000;

    EditText uiHost;
    Button uiConnect;

    @Override
    protected void onCreate(Bundle savedInstanceState)
    {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        createNotificationChannel();

        uiHost = findViewById(R.id.host);
        uiConnect = findViewById(R.id.connect);

        uiHost.setText(getPreferences(MODE_PRIVATE).getString("host", ""));

        uiHost.addTextChangedListener(new TextWatcher()
        {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) { }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) { }

            @SuppressLint("ApplySharedPref")
            @Override
            public void afterTextChanged(Editable s)
            {
                getPreferences(MODE_PRIVATE)
                    .edit()
                    .putString("host", s.toString())
                    .commit();
            }
        });

        uiConnect.setOnClickListener(new View.OnClickListener()
        {
            @Override
            public void onClick(View v)
            {
                if(uiConnect.getText() == getString(R.string.connect))
                {
                    uiConnect.setText(R.string.disconnect);
                    getAlerts(uiHost.getText().toString(), getTimestamp());
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


    private void getAlerts(final String host, final Long timestamp)
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
                            getAlerts(host, lastTimestamp);
                        }
                    }
                }, new Response.ErrorListener()
        {
            @Override
            public void onErrorResponse(VolleyError error)
            {
                getAlerts(host, timestamp);
            }
        });

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
        NotificationCompat.Builder builder = new NotificationCompat.Builder(this, CHANNEL_ID)
                .setSmallIcon(android.R.drawable.stat_sys_warning)
                .setContentTitle(getAlertTitle(alert.getType()))
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
            return "Dispositivo desconocido";
        }
        else if(Alert.TYPE_INTERMITTENT_POWER.equals(type))
        {
            return "Funcionamiento intermitente";
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
            return String.format("Ha aparecido un nuevo dispositivo \"%s\" en la red.", device);
        }
        else if(Alert.TYPE_INTERMITTENT_POWER.equals(type))
        {
            return String.format("El dispositivo \"%s\" se ha reiniciado múltiples veces de manera súbita.", device);
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
