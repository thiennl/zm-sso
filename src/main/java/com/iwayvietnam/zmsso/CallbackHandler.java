/*
 * ***** BEGIN LICENSE BLOCK *****
 * Zm SSO is the the  Zimbra Collaboration Open Source Edition extension Single Sign On authentication to the Zimbra Web Client.
 * Copyright (C) 2020-present iWay Vietnam and/or its affiliates. All rights reserved.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>
 * ***** END LICENSE BLOCK *****
 *
 * Zimbra Hierarchical Address Book
 *
 * Written by Nguyen Van Nguyen <nguyennv1981@gmail.com>
 */
package com.iwayvietnam.zmsso;

import com.iwayvietnam.zmsso.pac4j.SettingsBuilder;
import com.zimbra.cs.extension.ExtensionException;
import org.pac4j.core.context.JEEContext;
import org.pac4j.core.engine.DefaultCallbackLogic;
import org.pac4j.core.http.adapter.JEEHttpActionAdapter;
import org.pac4j.core.util.Pac4jConstants;

import javax.servlet.ServletException;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;
import java.io.IOException;

/**
 * @author Nguyen Van Nguyen <nguyennv1981@gmail.com>
 */
public class CallbackHandler extends BaseSsoHandler {
    private static final String CALLBACK_HANDLER_PATH = "callback";

    public CallbackHandler() throws ExtensionException {
        super();
    }

    @Override
    public String getPath() {
        return CALLBACK_HANDLER_PATH;
    }
    @Override
    public void doPost(final HttpServletRequest request, final HttpServletResponse response) throws IOException, ServletException {
        final String defaultUrl = Pac4jConstants.DEFAULT_URL_VALUE;
        final boolean saveInSession = true;
        final boolean multiProfile = true;
        final boolean renewSession = false;
        final JEEContext context = new JEEContext(request, response);
        DefaultCallbackLogic.INSTANCE.perform(context, config, JEEHttpActionAdapter.INSTANCE, defaultUrl, multiProfile, saveInSession, renewSession, SettingsBuilder.defaultClient().getName());
    }

    @Override
    public void doGet(final HttpServletRequest request, final HttpServletResponse response) throws IOException, ServletException {
        doPost(request, response);
    }
}
