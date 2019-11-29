package es.ontic.sot;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.text.Editable;
import android.text.TextWatcher;

public class PersistentTextWatcher implements TextWatcher
{
    private Activity activity;
    private String preference;

    public PersistentTextWatcher(Activity activity, String preference)
    {
        this.activity = activity;
        this.preference = preference;
    }

    @Override
    public void beforeTextChanged(CharSequence s, int start, int count, int after) { }

    @Override
    public void onTextChanged(CharSequence s, int start, int before, int count) { }

    @SuppressLint("ApplySharedPref")
    @Override
    public void afterTextChanged(Editable s)
    {
        activity
            .getPreferences(Context.MODE_PRIVATE)
            .edit()
            .putString(preference, s.toString())
            .commit();
    }
}
