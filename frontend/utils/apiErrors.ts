import { FetchError } from 'ofetch'
import type { ValidationErrorResponse } from '~/types/api'

export interface FormErrorState {
  message: string
  fieldErrors: Record<string, string[]>
}

const GENERIC_ERROR_MESSAGE = 'Something went wrong. Please try again.'

/**
 * Convert an API error into a display-ready form error state,
 * mapping Laravel 422 validation responses to per-field messages.
 */
export function toFormErrorState(error: unknown): FormErrorState {
  if (error instanceof FetchError && error.statusCode === 422) {
    const response = error.data as ValidationErrorResponse

    return {
      message: response.message ?? GENERIC_ERROR_MESSAGE,
      fieldErrors: response.errors ?? {},
    }
  }

  if (error instanceof FetchError && error.statusCode === 429) {
    return {
      message: 'Too many attempts. Please wait a minute and try again.',
      fieldErrors: {},
    }
  }

  return { message: GENERIC_ERROR_MESSAGE, fieldErrors: {} }
}
