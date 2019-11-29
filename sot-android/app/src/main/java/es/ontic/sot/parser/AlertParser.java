package es.ontic.sot.parser;

import org.json.JSONArray;
import org.json.JSONException;
import org.json.JSONObject;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.Iterator;
import java.util.List;
import java.util.Map;

import es.ontic.sot.model.Alert;

public class AlertParser
{
    public List<Alert> parseAll(String text) throws JSONException
    {
        final List<Alert> alerts = new ArrayList<>();
        final JSONArray objects = new JSONArray(text);

        for(int i=0; i<objects.length(); i++)
        {
            alerts.add(parse(objects.getJSONObject(i)));
        }

        return alerts;
    }

    private Alert parse(JSONObject object) throws JSONException
    {
        final String type = object.getString("type");
        final long timestamp = object.getLong("timestamp");
        final int priority = object.getInt("priority");
        final Map<String, String> data = parseData(object.getJSONObject("data"));

        return new Alert(type, data, timestamp, priority);
    }

    private Map<String, String> parseData(JSONObject object) throws JSONException
    {
        final HashMap<String, String> data = new HashMap<>();
        final Iterator<String> keys = object.keys();

        while(keys.hasNext())
        {
            final String key = keys.next();
            final String value = object.getString(key);
            data.put(key, value);
        }

        return data;
    }
}
