import React from 'react'
import { cn } from '../../lib/utils'
type InputProps = React.InputHTMLAttributes<HTMLInputElement> & {
  labelClassName?: React.HTMLAttributes<HTMLInputElement>['className']
  inputClassName?: React.HTMLAttributes<HTMLInputElement>['className']
  className?: string
  label?: string
  placeholder?: string
  value?: string
  onChange?: (event: React.ChangeEvent<HTMLInputElement>) => void
  onChangeText?: (event: React.ChangeEvent<HTMLInputElement['value']>) => void
  type?: React.HTMLAttributes<HTMLInputElement>['type']
  name?: React.HTMLAttributes<HTMLInputElement>['name']
  id?: React.HTMLAttributes<HTMLInputElement>['id']
}
const Input = ({
  labelClassName,
  inputClassName,
  className,
  label,
  placeholder,
  value,
  onChange = () => null,
  onChangeText = () => null,
  type,
  name,
  id,
  ...rest
}: InputProps) => {
  return (
    <div className={(cn(className))}>
      <label htmlFor={id} className={cn("block mb-2 text-sm font-medium text-gray-900 dark:text-white", labelClassName)}>
        {label}
      </label>
      <input
        type={type}
        name={name}
        id={id}
        className={cn("bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-600 dark:border-gray-500 dark:placeholder-gray-400 dark:text-white", inputClassName)}
        value={value}
        onChange={(e) => {
          onChange(e)
          onChangeText(e.target.value)
        }}
        placeholder={placeholder}
        {...rest} />
    </div>
  )
}

export default Input