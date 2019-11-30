package es.ontic.sot.model;

import java.util.Map;

public class Alert
{
    public static final String TYPE_NEW_DEVICE = "new_device";
    public static final String TYPE_INTERMITTENT_POWER = "intermittent_power";
    public static final String TYPE_ACTIVATION = "activation";
    public static final String TYPE_DANGEROUS_ACTIVATION = "dangerous_activation";
    public static final String TYPE_INCOSISTENCY = "inconsistency";
    public static final String TYPE_DEVICE_OFFLLINE = "device_online";

    private String type;
    private Map<String, String> data;
    private long timestamp;
    private int priority;

    public Alert(String type, Map<String, String> data, long timestamp, int priority)
    {
        this.type = type;
        this.data = data;
        this.timestamp = timestamp;
        this.priority = priority;
    }

    public String getType()
    {
        return type;
    }

    public Map<String, String> getData()
    {
        return data;
    }

    public long getTimestamp()
    {
        return timestamp;
    }

    public int getPriority()
    {
        return priority;
    }

    public String getDevice()
    {
        return data.get("device");
    }
}
